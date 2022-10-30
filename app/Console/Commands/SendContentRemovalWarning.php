<?php namespace MotionArray\Console\Commands;

use Illuminate\Console\Command;
use MotionArray\Mailers\UserMailer;
use MotionArray\Models\User;
use MotionArray\Repositories\UserRepository;
use Carbon\Carbon;

class SendContentRemovalWarning extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'motionarray:send-content-removal-warning';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send email to users warning them content will be removed soon';

    protected $user;

    protected $userMailer;

    protected $firstWarningDays = 7;
    protected $secondWarningDays = 3;
    protected $finalWarningDays = 1;


    public function __construct(UserRepository $user, UserMailer $userMailer)
    {
        $this->user = $user;

        $this->userMailer = $userMailer;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $this->sendUsersFinalWarning();
        $this->sendUsersSecondWarning();
        $this->sendUsersFirstWarning();

        $this->info('Finished sending warnings');
    }

    public function sendUsersFirstWarning()
    {

        $status = User::CONTENT_REMOVAL_NO_NOTIFICATION_STATUS;
        $date = Carbon::now()->addDays($this->firstWarningDays);
        $newStatus = User::CONTENT_REMOVAL_FIRST_NOTIFICATION_STATUS;

        $users = $this->user->getContentReadyForRemovalUsers($status, $date);

        $this->info('Sending first warning');

        $this->sendWarning($users, $this->firstWarningDays, $newStatus);

    }

    public function sendUsersSecondWarning()
    {

        $status = User::CONTENT_REMOVAL_FIRST_NOTIFICATION_STATUS;
        $date = Carbon::now()->addDays($this->secondWarningDays);
        $newStatus = User::CONTENT_REMOVAL_SECOND_NOTIFICATION_STATUS;

        $users = $this->user->getContentReadyForRemovalUsers($status, $date);

        $this->info('Sending second warning');

        $this->sendWarning($users, $this->secondWarningDays, $newStatus);

    }

    public function sendUsersFinalWarning()
    {

        $status = User::CONTENT_REMOVAL_SECOND_NOTIFICATION_STATUS;
        $date = Carbon::now()->addDays($this->finalWarningDays);
        $newStatus = User::CONTENT_REMOVAL_FINAL_NOTIFICATION_STATUS;

        $users = $this->user->getContentReadyForRemovalUsers($status, $date);

        $this->info('Sending final warning');

        $this->sendWarning($users, $this->finalWarningDays, $newStatus);

    }

    public function sendWarning($users, $days, $newStatus)
    {
        $users->chunk(100, function ($users) use ($days, $newStatus) {

            $this->info('Processing ' . count($users) . ' users');

            foreach ($users as $user) {
                $this->userMailer->contentRemovalWarning($user, $days);

                $user->content_removal_warning_status = $newStatus;
                $user->save();
            }
        });
    }
}
