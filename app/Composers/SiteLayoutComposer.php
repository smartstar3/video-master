<?php

namespace MotionArray\Composers;


class SiteLayoutComposer
{
    public function compose($view)
    {
        $socialNetworks = new \stdClass();
        $socialNetworks->vimeoUrl = 'https://vimeo.com/motionarray';
        $socialNetworks->twitterUrl = 'https://twitter.com/MotionArray';
        $socialNetworks->facebookUrl = 'https://www.facebook.com/motionarray';
        $socialNetworks->instagramUrl = 'https://www.instagram.com/motionarray';
        $socialNetworks->youtubeUrl = 'https://www.youtube.com/channel/UCQyoKfULtJaHqSxB80Efw4w';

        $supportEmail = 'hello@motionarray.com';

        $view->with('socialNetworks', $socialNetworks);
        $view->with('supportEmail', $supportEmail);
    }
}
