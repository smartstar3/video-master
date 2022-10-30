<?php namespace MotionArray\Http\Controllers\Site;

use MotionArray\Http\Controllers\Shared\RemindersController as RemindersController;
use Config;


class SiteRemindersController extends RemindersController
{

    public $getRemindView = "site.sessions.remind";
    public $getResetView = "site.sessions.reset";
    public $resetRedirect = "/account";

    public function __construct()
    {
        Config::set("auth.reminder.email", "site.emails.auth.reminder");
    }

}
