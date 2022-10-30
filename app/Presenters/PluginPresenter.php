<?php namespace MotionArray\Presenters;

use Auth;
use MotionArray\Helpers\Helpers;

class PluginPresenter extends ProductPresenter
{
    public function downloadLinkButton()
    {
        return "<a href=\"/plugins/download/\" class=\"download-btn  btn  btn--white  btn--icon\">" .
            "<span class=\"icon  icon--download\"></span>" .
            "<span class=\"btn__text\">Download Now</span>" .
            "</a>";
    }
}
