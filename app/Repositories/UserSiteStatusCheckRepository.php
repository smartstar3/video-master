<?php

namespace MotionArray\Repositories;

use Carbon\Carbon;
use MotionArray\Models\UserSiteStatusCheck;

class UserSiteStatusCheckRepository
{
    /**
     * @param array $attributes
     * @return bool
     */
    public function updateOrCreate(array $attributes): bool
    {
        $userSiteStatusCheck = UserSiteStatusCheck::updateOrCreate(
            ['user_site_id' => $attributes['user_site_id'], 'domain' => $attributes['domain']],
            ['is_success' => $attributes['is_success'], 'last_request_timestamp' => Carbon::now()]
        );

        return $userSiteStatusCheck->save();
    }
}
