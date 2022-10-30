<?php namespace MotionArray\Models;

use Illuminate\Support\Facades\Hash;

class ProjectReviewSetting extends BaseModel
{
    protected $hidden = ['password'];
}

ProjectReviewSetting::saving(function ($settings) {
    if (isset($settings->uses_password)) {
        unset($settings->uses_password);
    }

    if (isset($settings->newpassword)) {
        $settings->password = Hash::make($settings->newpassword);

        unset($settings->newpassword);
    }
});