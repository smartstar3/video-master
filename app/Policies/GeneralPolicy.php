<?php

namespace MotionArray\Policies;

use MotionArray\Models\User;
use MotionArray\Services\FeatureFlag\FeatureFlagService;

class GeneralPolicy
{
    /**
     * @var FeatureFlagService
     */
    private $featureFlagService;

    public function __construct(FeatureFlagService $featureFlagService)
    {
        $this->featureFlagService = $featureFlagService;
    }

    public function feature(User $user, string $feature): bool
    {
        return $this->featureFlagService->check($feature, $user);
    }
}
