<?php

namespace MotionArray\Providers\Deferred;

use Illuminate\Support\ServiceProvider;
use MotionArray\Models\StaticData\FeatureFlags;
use MotionArray\Repositories\FeatureFlagRepository;
use MotionArray\Services\FeatureFlag\FeatureFlagService;

class FeatureFlagServiceProvider extends ServiceProvider
{
    protected $defer = true;

    public function register()
    {
        $this->app->singleton(FeatureFlagService::class, function () {
            $repo = $this->app->make(FeatureFlagRepository::class);
            $featureFlags = app(FeatureFlags::class)->dataCollection()->pluck('slug')->toArray();
            $state = config('feature-flags.global_state');
            $submissionState = config('feature-flags.submission_state');

            return new FeatureFlagService($repo, $featureFlags, $state, $submissionState);
        });
    }

    public function provides()
    {
        return [FeatureFlagService::class];
    }
}
