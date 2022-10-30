<?php

namespace MotionArray\Providers\Deferred;

use Illuminate\Support\ServiceProvider;
use MotionArray\Services\Aws\CloudFront\UrlSigner;

class AwsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UrlSigner::class, function() {
            return new UrlSigner(config('aws.cloudfront_key_pair_id'), config('aws.cloudfront_pk_path'));
        });
    }
}
