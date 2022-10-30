<?php namespace MotionArray\Http\Controllers\Admin;

use MotionArray\Http\Controllers\Shared\SessionsController as SessionsController;

class UsersController extends SessionsController
{

    public $createView = "admin.sessions.create";
    public $intendedDefault = "mabackend";
    public $destroyRedirect = "mabackend";
    public $accessLevel = [
        1, // Super Admin
        2, // Admin
        // 3  // Seller
    ];
    public $forceRememberMe = true;

}
