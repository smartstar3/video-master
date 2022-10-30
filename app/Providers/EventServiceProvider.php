<?php

namespace MotionArray\Providers;

use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Illuminate\Auth\Events\Login::class => [
            \MotionArray\Listeners\LogSuccessfulLogin::class,
        ],
        \Laravel\Passport\Events\AccessTokenCreated::class => [
            \MotionArray\Listeners\RevokeOldTokens::class,
        ],

        \MotionArray\Events\ProductUnpublished::class => [
            \MotionArray\Listeners\DeletePreviewFromVimeo::class,
            \MotionArray\Listeners\DeletePreviewFromYouTube::class,
            \MotionArray\Listeners\DeleteProductFromAlgolia::class,
        ],

        \MotionArray\Events\SubmissionApproved::class => [
            \MotionArray\Listeners\RequestCompleted::class
        ],

        \MotionArray\Events\PortfolioSaved::class => [
            \MotionArray\Listeners\UploadScreenshotToS3::class
        ],

        /**
         * Encoding events
         */
        \MotionArray\Events\Encoder\ReadyToEncode::class => [
            \MotionArray\Listeners\Encoder\EncodePreviews::class
        ],
        \MotionArray\Events\Encoder\EncondingDone::class => [
            \MotionArray\Listeners\Encoder\StorePreviews::class
        ],
        \MotionArray\Events\Encoder\PreviewsStored::class => [
            \MotionArray\Listeners\Encoder\CleanUp::class
        ],
        \MotionArray\Events\Encoder\EncondingCancelled::class => [
            \MotionArray\Listeners\Encoder\CleanUp::class
        ],

        \MotionArray\Events\SubscriptionUpgraded::class => [
            \MotionArray\Listeners\CheckForSuspiciousActivity::class,
        ],
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
