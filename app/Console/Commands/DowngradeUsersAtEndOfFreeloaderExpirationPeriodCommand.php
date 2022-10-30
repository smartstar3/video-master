<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use MotionArray\Events\UserEvent\UserFreeloaderExpired;
use MotionArray\Models\User;
use MotionArray\Models\Plan;
use Carbon\Carbon;
use App;
use MotionArray\Support\UserEvents\UserEventLogger;

class DowngradeUsersAtEndOfFreeloaderExpirationPeriodCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:downgrade-users-at-end-of-freeloader-expiration-period';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downgrade all freeloader users that have reached their expiration date.';

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
        $userRepo = App::make("MotionArray\Repositories\UserRepository");

        $users = User::where('access_expires_at', '<=', Carbon::parse('today'))->get();

        $i = 0;
        foreach ($users as $user) {
            $userRepo->downgrade($user->id);
            $logger->log(new UserFreeloaderExpired($user->id));
            $i++;
        }

        $this->info($i . ' users downgraded.');
    }
}
