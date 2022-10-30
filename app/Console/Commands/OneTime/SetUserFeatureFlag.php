<?php

namespace MotionArray\Console\Commands\OneTime;

use Illuminate\Console\Command;
use MotionArray\Models\User;
use MotionArray\Repositories\FeatureFlagRepository;

class SetUserFeatureFlag extends Command
{
    protected $signature = 'ma:set-user-feature-flag
             {--email= : Email of user}
             {--feature= : feature flag to set}
             {--value=1 : value to set 1 or 0}';

    protected $description = 'Set feature flag value for a user';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $email = $this->option('email');
        $feature = $this->option('feature');
        $value = (bool)$this->option('value');


        $user = User::query()->where('email', $email)->first();

        if (!$user) {
            $this->error('user not found');
            return;
        }

        if(!$feature){
            $this->error('feature is required');
            return;
        }
        app(FeatureFlagRepository::class)->setUserFeatureFlag($user, $feature, $value);
    }
}
