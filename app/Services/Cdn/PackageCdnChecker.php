<?php

namespace MotionArray\Services\Cdn;

use Config;
use MotionArray\Models\User;

class PackageCdnChecker
{
    protected $useCdn = false;
    protected $useCdnAdmin = false;

    public function __construct()
    {
        $this->useCdn = Config::get('aws.packages_use_cdn');
        $this->useCdnAdmin = Config::get('aws.packages_use_cdn_admin');
    }

    public function shouldUseCDN(User $user = null): bool
    {
        if ($user === null) {
            return false;
        }

        if ($user->isAdmin()) {
            $useCdn = $this->useCdnAdmin;
        } else {
            $useCdn = $this->useCdn;
        }

        return $useCdn;
    }
}
