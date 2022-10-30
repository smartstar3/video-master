<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use MotionArray\Models\User;
use MotionArray\Models\Plan;
use Carbon\Carbon;

class DowngradeUsersOutsideGracePeriodCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:downgrade-users-outside-grace-period';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Downgrade all users that have cancelled their subscription and are now outside their grace periods.';

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
        $users = User::where('subscription_ends_at', '<=', Carbon::parse("today"))->get();
        $freePlan = Plan::where('billing_id', '=', 'free')->first();

        $i = 0;
        foreach ($users as $user) {
            $user->plan_id = $freePlan->id;
            $user->save();

            $i++;
        }

        $this->info($i . ' users downgraded to the Free plan.');
    }
}
