<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use MotionArray\Models\User;
use MotionArray\Models\Plan;
use Carbon\Carbon;
use App;

class FreeloadersDueToExpireCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:email-admin-about-freeloaders-due-to-expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email admin regarding freeloaders due to expire.';

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
        $feedbackMailer = App::make("MotionArray\Mailers\FeedbackMailer");

        $users = User::whereBetween('access_expires_at', [Carbon::parse("today")->addDays(7), Carbon::parse("today")->addDays(8)])
            ->get();

        $i = 0;
        foreach ($users as $user) {
            $feedbackMailer->freeloaderDueToExpire($user);

            $i++;
        }

        $this->info($i . ' users downgraded.');
    }
}
