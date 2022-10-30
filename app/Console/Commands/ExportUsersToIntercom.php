<?php
namespace MotionArray\Console\Commands;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use MotionArray\Models\StaticData\PaymentGateways;
use MotionArray\Models\User;
use MotionArray\Repositories\UserRepository;
use MotionArray\Models\Product;
use App;
use AWS;
use Config;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use MotionArray\Services\Intercom\IntercomService;

/**
 * This class exports our users data to intercom
 * we limit the number of users we export to reduce our costs
 *
 * The exported users includes:
 * - All Sellers
 * - All users that ever had a subscription
 * - Fill in the rest with free users in order of priority by last_login
 *
 * We register a user whenever its logged through the chat client (JS)
 * So this scripts also makes sure to remove the excess of free users that didnt logged recently
 *
 *
 * Class ExportUsersToIntercom
 * @package MotionArray\Console\Commands
 */
class ExportUsersToIntercom extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'motionarray:export-users-to-intercom
        {--update-intercom-ids : import users from Intercom and upate on the database}
        {--update-intercom-ids-forced : updates intercom_id even if it matches}
        {--new-users : ignore users already having intercom_id}
        {--paypal-users : just send users who attempted to use Paypal}
        {--last-day : only people logged in in the last day}
        {--offset=0 : the offset to start from}
        ';

    protected $description = 'Exports the premium users and last 3 month free users to Intercom';

    /**
     * To limit our costs we limit the number of users intercom holds
     *
     * @var int
     */
    protected $limit;

    /**
     * @var UserRepository
     */
    private $user;

    /**
     * @var IntercomService
     */
    private $intercom;

    /**
     * Microsec delay between requests for API throttling
     *
     * @var int
     */
    private $delay = 100 * 1000;

    /**
     * Chunks to process results in
     *
     * @var integer
     */
    private $chunkSize = 1000;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserRepository $user, IntercomService $intercom)
    {
        $this->user = $user;

        $this->intercom = $intercom;

        $this->limit = (int) config('services.intercom.user_limit');

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        ini_set('memory_limit', '-1');
        if ($this->option('update-intercom-ids') || $this->option('update-intercom-ids-forced')) {
            $this->updateIntercomIds();

            return;
        }

        $offset = $this->option('offset');

        // Get Sellers
        $sellersQuery = $this->sellersQuery();

        // Get Paying Users
        $payingUsersQuery = $this->payingUsersQuery();

        // Get Additional Users
        $limit = max($this->limit - $sellersQuery->count() - $payingUsersQuery->count(), 0);
        $additionalUsersQuery = $this->additionalUsersQuery($sellersQuery, $payingUsersQuery, $limit);

        // Remove excess users
        $this->removeExtraUsers($sellersQuery, $payingUsersQuery, $additionalUsersQuery);

        // Apply --last-login and --new-users filters on the queries.
        // Filters should be applied after removeExtraUsers, as it needs the list of all users that need to be on Intercom.
        $sellersQuery = $this->addFilters($sellersQuery);
        $this->info('Found ' . $sellersQuery->count() . ' sellers.');

        $payingUsersQuery = $this->addFilters($payingUsersQuery);
        $this->info('Found ' . $payingUsersQuery->count() . ' paying users.');

        $additionalUsersQuery = $this->addFilters($additionalUsersQuery);
        $this->info("Found {$limit} additional users.");

        // Send sellers
        $this->sendUsersToIntercom($sellersQuery, $offset);

        // Send paying users
        $this->sendUsersToIntercom($payingUsersQuery, $offset);

        // Send additional users
        $this->sendUsersToIntercom($additionalUsersQuery, $offset);

    }

    /**
     * Remove excess users from Intercom to add space
     * Any user that has intercom_id but is not in the 3 lists is excess
     *
     * @param Builder $sellersQuery
     * @param Builder $payingUsersQuery
     * @param Builder $additionalUsersQuery
     *
     * @return void
     */
    protected function removeExtraUsers($sellersQuery, $payingUsersQuery, $additionalUsersQuery)
    {
        $sellersQuery = clone $sellersQuery; // Without clone, select will be applied on the object
        $payingUsersQuery = clone $payingUsersQuery;
        $additionalUsersQuery = clone $additionalUsersQuery;

        // Get any user that is on intercom, but shouldn't be according to the 3 queries
        $excessUsersQuery = User::withTrashed()
            ->whereNotIn('id', $sellersQuery->select('id'))
            ->whereNotIn('id', $payingUsersQuery->select('id'))
            // Because $additionalUsersQuery has LIMIT, it needs to be a temp table.
            ->whereRaw('id NOT IN (SELECT * FROM (' . $additionalUsersQuery->select('id')->toSql() . ') as temp)')
            ->mergeBindings($additionalUsersQuery->getQuery())
            ->whereNotNull('intercom_id');

        $this->info('Deleting ' . $excessUsersQuery->count() . ' extra Intercom users...');

        $index = 0;
        $count = 0;
        // $excessUsersQuery->chunk($this->chunkSize, function($users) use (&$count, &$index) {
        foreach ($excessUsersQuery->cursor() as $user) {
            $index++;
            $this->info("( {$index} ) " . $user->email);

            if ($this->removeUserFromIntercom($user)) {
                $count++;
            }
            usleep($this->delay);
        }
        // });

        $this->info("{$count} users deleted from Intercom.");
    }

    /**
     * Remove a user from Intercom
     *
     * @param User $user
     *
     * @return bool
     */
    protected function removeUserFromIntercom(User $user)
    {
        try {
            $response = $this->intercom->deleteUser($user->intercom_id);
            $result = true;
        } catch (ClientException $e) {
            $this->warn($e->getMessage());
            $this->info('User not found on Intercom, id: ' . $user->intercom_id);
            $result = false;
        }

        $user->intercom_id = null;
        $user->save();

        return $result;
    }

    /**
     * Send an array of users to Intercom
     * Will skip until offset is met
     *
     * @param Collection $users
     *
     * @return int number of users sent
     */
    protected function sendUsersToIntercom($users, $offset)
    {
        static $index = 0;
        $count = 0;

        $this->info("[ {$index} ] Sending  " . $users->count() . " users to Intercom...");
        foreach ($users->cursor() as $user) {
            if ($index++ < $offset)
                continue;

            $this->info("( {$index} ) {$user->email}");

            if ($this->sendUserToIntercom($user))
                $count++;

            usleep($this->delay);
        }
        $this->info("{$count} updates on Intercom succeeded.");

        return $count;
    }

    /**
     * Send a user to Intercom
     *
     * @param User $user
     * @return bool
     */
    protected function sendUserToIntercom(User $user)
    {
        $userData = $this->user->getIntercomData($user, true);

        try {
            $intercomUser = $this->intercom->createUser($userData);

            $user->intercom_id = $intercomUser->id;

            $user->save();
        } catch (Exception $e) {
            $this->error('Error saving user on intercom: Error code: ' . $e->getCode() . '. ' . $e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Adds filters to the query based on command options/parameters
     *
     * @param \Illuminate\Database\Query\Builder $query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function addFilters($query)
    {
        if ($this->option('last-day')) {
            $lastLogin = Carbon::now()->subDay();
            $query->where('last_login', '>', $lastLogin);
        }

        if ($this->option('new-users')) {
            $query->whereNull('intercom_id');
        }

        if ($this->option('paypal-users')) {
            $query->whereHas('subscriptions', function($query) {
                return $query->where('payment_gateway_id', PaymentGateways::PAYPAL_ID);
            });
        }

        return $query;
    }

    /**
     * Get users with stripe_id (ever made payment)
     *
     * @return Builder
     */
    protected function payingUsersQuery()
    {
        return User::where(function(Builder $query) {
            return $query->whereNotNull('stripe_id')
                ->orWhereHas('activeSubscription');
        })
            // ->where('confirmed', '=', 1) // for now, have all paying customers
            ;

    }

    /**
     * Get sellers query
     *
     * @return \Illuminate\Database\Query\Builder
     */
    protected function sellersQuery()
    {
        return User::whereIn('id', function ($query) {
            $query->from((new Product)->getTable())
                ->select('seller_id')
                ->distinct();
            })
            // ->where('confirmed', '=', 1) // for now, have all sellers
            ;
    }

    /**
     * Get users that never paid
     * Sorted by latest login time, limited by $this->limit
     *
     * @param $sellersQuery
     * @param $payingUsersQuery
     * @param $limit
     *
     * @return \Illuminate\Database\Query\Builder ($limit, $query)
     */
    protected function additionalUsersQuery($sellersQuery, $payingUsersQuery, $limit)
    {
        $sellersQuery = clone $sellersQuery; // Without clone, select will be applied on the object
        $payingUsersQuery = clone $payingUsersQuery;

        $query = User::whereNull('stripe_id')
            ->whereNotIn('id', $sellersQuery->select('id'))
            ->whereNotIn('id', $payingUsersQuery->select('id'))
            ->where('confirmed', '=', 1)
            ->orderBy('last_login', 'DESC')
            ->limit($limit);

        return $query;
    }
    /**
     * Import users from Intercom and update IDs on database
     * Deletes those not found in the database from Intercom
     * TODO: redo or remove this, doesn't seem to be used anywhere in the code
     * @return void
     */
    private function updateIntercomIds()
    {
        $force = $this->option('update-intercom-ids-forced');

        $scroll_param = null;

        $page = 0;

        do {
            $response = $this->intercom->scrollUsers(['scroll_param' => $scroll_param]);

            if (isset($response->scroll_param)) {
                $scroll_param = $response->scroll_param;
            }

            $page++;

            $this->info('Importing ' . count($response->users) . ' intercom users. Page ' . $page);

            foreach ($response->users as $intercomUser) {
                $user = User::where('email', '=', $intercomUser->email)->orWhere('intercom_id', '=', $intercomUser->id)->first();

                if ($user) {
                    if (!$force && $intercomUser->id == $user->intercom_id) {
                        continue;
                    }

                    $user->intercom_id = $intercomUser->id;

                    $user->save();
                } else {
                    $this->info('User not found in DB: ' . $intercomUser->email);

                    $this->intercom->deleteUser($intercomUser->id);
                }
            }

            usleep($this->delay);

        } while (isset($response->users) && count($response->users));
    }
}
