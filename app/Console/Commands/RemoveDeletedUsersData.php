<?php namespace MotionArray\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use MotionArray\Models\User;
use MotionArray\Repositories\UserRepository;

class RemoveDeletedUsersData extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:remove-deleted-users-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove deleted users data after 30 days';

    /**
     * Submission repo
     */
    private $userRepo;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepo)
    {

        $this->userRepo = $userRepo;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cutoff = Carbon::now()->subDays(30);

        //Get list of deleted users older than 30 days without data being cleared
        User::onlyTrashed()->dataNotRemoved()->where('deleted_at', '<', $cutoff)->orderBy('deleted_at', 'ASC')->chunk(100, function ($users) {
            foreach ($users as $user) {
                $this->info('Processed user ' . $user->id);
                $this->userRepo->removeDeletedUserData($user);
            }
        });
    }
}
