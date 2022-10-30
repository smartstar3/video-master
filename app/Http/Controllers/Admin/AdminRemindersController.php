<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Http\Controllers\Shared\RemindersController as RemindersController;
use Config;

class AdminRemindersController extends RemindersController
{

    public $getRemindView = "admin.sessions.remind";
    public $getResetView = "admin.sessions.reset";
    public $resetRedirect = "/mabackend";

    public function __construct()
    {
        Config::set("auth.reminder.email", "admin.emails.auth.reminder");
    }

}
