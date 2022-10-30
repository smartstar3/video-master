<?php namespace MotionArray\Console\Commands\OneTime;

use Illuminate\Console\Command;
use MotionArray\Models\UserToken;
use MotionArray\Repositories\UserRepository;
use Config;
use MotionArray\Services\Intercom\IntercomService;

#TODO(abx): seems like this is unused.
class IntercomUpdatePluginUsers extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'motionarray-onetime:intercom-update-plugin-users';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates intercom data for premium users using the plugins';

    protected $user;

    protected $intercom;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository, IntercomService $intercom)
    {
        $this->user = $userRepository;

        $this->intercom = $intercom;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $userTokens = UserToken::orderBy('user_id', 'ASC')->get();

        foreach ($userTokens as $userToken) {
            $this->info('Updating intercom data for user ' . $userToken->user_id);

            $userData = $this->user->getIntercomData($userToken->user, true);

            $intercomUser = $this->intercom->createUser($userData);
        }
    }
}
