<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use MotionArray\Mailers\UserMailer;
use MotionArray\Models\User;
use Carbon\Carbon;
use App;

class FindStripeActiveErrors extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:find-stripe-active-errors';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Find users with an error on stripe_active field.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        User::where('stripe_id', '!=', 'NULL')
            ->where('stripe_active', '!=', '0')
            ->chunk(50, function($users) {
                foreach ($users as $user) {
                    try {
                        if ($user->subscription()) {
                            $stripeCustomer = $user->subscription()->getStripeCustomer();

                            if (!$stripeCustomer->subscriptions->total_count) {
                                $this->info('User ' . $stripeCustomer->id. ' has no subscriptions');
                            }
                        }
                    } catch (Stripe_InvalidRequestError $e) {
                        $this->info('Could not update record for user: ' . $user->email . ': ' . $e->getMessage());
                    } catch (Stripe_Error $e) {
                        $this->info('Could not update record for user: ' . $user->email . ': ' . $e->getMessage());
                    } catch (Exception $e) {
                        $this->info('Could not update record for user: ' . $user->email . ': ' . $e->getMessage());
                    }
                }
            });
    }
}
