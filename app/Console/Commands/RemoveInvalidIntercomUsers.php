<?php namespace MotionArray\Console\Commands;

use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use MotionArray\Models\User;
use MotionArray\Services\Intercom\IntercomService;

/**
 * This command removes the intercom_id param from the users that are not registered on intercom
 * This was meant to be a temporal fix, and shouldnt be necessary if the update script is done correctly
 *
 * Class RemoveInvalidIntercomUsers
 * @package MotionArray\Console\Commands
 */
class RemoveInvalidIntercomUsers extends Command
{
    protected $name = 'motionarray:remove-invalid-intercom-users';

    protected $description = 'Remove intercom users that doesnt exist on the DB (Pulled from stripe).';

    public function __construct(IntercomService $intercom)
    {
        $this->intercom = $intercom;

        parent::__construct();
    }

    public function handle()
    {
        $this->getExistingIntercomIds(function ($intercomIds) {
            $this->deleteMissingIntercomsUsers($intercomIds);
        });
    }

    /**
     * Get intercomIds from users on intercom
     *
     * @return array
     */
    private function getExistingIntercomIds($callback)
    {
        $scroll_param = null;

        do {
            $existingUsers = [];

            $response = $this->intercom->scrollUsers(['scroll_param' => $scroll_param]);

            if (isset($response->scroll_param)) {
                $scroll_param = $response->scroll_param;
            }

            foreach ($response->users as $intercomUser) {
                $existingUsers[] = $intercomUser->id;
            }

            $callback($existingUsers);

        } while (isset($response->users) && count($response->users));

        return $existingUsers;
    }

    private function deleteMissingIntercomsUsers($intercomIds)
    {
        $dbIntercomIds = User::withTrashed()
            ->whereIn('intercom_id', $intercomIds)
            ->whereNotNull('intercom_id')
            ->pluck('intercom_id')
            ->toArray();

        $deleteIds = array_diff($intercomIds, $dbIntercomIds);

        $this->info('Deleting ' . count($deleteIds) . ' intercom users');

        foreach ($deleteIds as $intercomId) {
            $this->info('Delete intercom user: ' . $intercomId);

            try {
                $response = $this->intercom->deleteUser($intercomId);
            } catch (ClientException $e) {
                $this->info($e->getMessage());
                $this->info('User not found, id: ' . $intercomId);
            }

            usleep(400000);
        }
    }

}
