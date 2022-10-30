<?php

namespace MotionArray\Services\FeatureFlag;

use InvalidArgumentException;
use MotionArray\Models\User;
use MotionArray\Repositories\FeatureFlagRepository;

class FeatureFlagService
{
    /**
     * @var FeatureFlagRepository
     */
    private $featureFlagRepo;

    /**
     * @var array
     */
    protected $featureFlags;

    /**
     * @var array
     */
    private $state;

    /**
     * @var array
     */
    private $submissionState;

    public function __construct(
        FeatureFlagRepository $featureFlagRepo,
        array $featureFlags,
        array $state = [],
        array $submissionState = []
    )
    {
        $this->featureFlagRepo = $featureFlagRepo;
        $this->featureFlags = $featureFlags;

        $state = array_only($state, $this->featureFlags);
        $this->state = $state;

        $submissionState = array_only($submissionState, $this->featureFlags);
        $this->submissionState = $submissionState;
    }

    public function check(
        string $feature,
        User $user = null,
        $isRelatedMarketplace = false
    )
    {
        if (!in_array($feature, $this->featureFlags)) {
            throw new InvalidArgumentException($feature . ' is not a valid feature flag');
        }

        $enabledGlobally = $this->state[$feature] ?? false;
        if ($enabledGlobally) {
            return true;
        }

        $enabledSubmission = $this->submissionState[$feature] ?? false;
        if ($enabledSubmission && !$isRelatedMarketplace) {
            return true;
        }

        if ($user) {
            return $this->featureFlagRepo->checkUserHasFeatureFlag($user, $feature);
        }

        return false;
    }
}
