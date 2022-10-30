<?php

namespace MotionArray\Http\Controllers\Admin;

use View;
use Request;


class SiteSettingsController extends BaseController
{


    public function __construct()
    {

    }


    public function siteSettings()
    {


        return View::make('admin.site-settings');
    }


    public function updateSiteSettings()
    {


        if (Request::has('enable_site_maintenance_warning_message')) {
            \SiteSetting::set('enable_site_maintenance_warning_message', 'true');
        } else {
            \SiteSetting::set('enable_site_maintenance_warning_message', 'false');
        }

        \SiteSetting::set('site_maintenance_warning_message', Request::input('site_maintenance_warning_message'));

        \SiteSetting::save();

        return redirect()->action('Admin\SiteSettingsController@siteSettings');


    }

}