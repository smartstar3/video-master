<?php

namespace MotionArray\Repositories;

use DB;
use MotionArray\Models\FeatureFlag;
use MotionArray\Models\StaticData\FeatureFlags;
use MotionArray\Models\User;

class FeatureFlagRepository
{
    /**
     * @var FeatureFlags
     */
    private $featureFlags;

    public function __construct(FeatureFlags $featureFlags)
    {
        $this->featureFlags = $featureFlags;
    }

    public function checkUserHasFeatureFlag(User $user, string $feature): bool
    {
        $featureFlagId = $this->slugToId($feature);
        return DB::table('feature_flag_users')
            ->where('user_id', $user->id)
            ->where('feature_flag_id', $featureFlagId)
            ->exists();
    }

    public function setUserFeatureFlag(User $user, $featureIdOrSlug, bool $value = true)
    {
        if (is_numeric($featureIdOrSlug)) {
            $featureFlagId = $featureIdOrSlug;
        } else {
            $featureFlagId = $this->slugToId($featureIdOrSlug);
        }

        $featureFlag = FeatureFlag::find($featureFlagId);

        if ($value) {
            $featureFlag->users()->sync($user->id);
        } else {
            $featureFlag->users()->detach($user->id);
        }
    }

    protected function slugToId(string $feature)
    {
        $featureFlagId = $this->featureFlags->slugToId($feature);
        if (!$featureFlagId) {
            throw new \InvalidArgumentException($feature . ' is not a valid feature flag.');
        }
        return $featureFlagId;
    }
}
