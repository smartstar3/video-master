<?php

namespace MotionArray\Jobs;

use MotionArray\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use MotionArray\Mailers\UserMailer;
use MotionArray\Models\User;

class SendSellerReviewNotificationEmail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $seller;

    /**
     * Create a new job instance.
     *
     * @param User $seller
     * @return void
     */
    public function __construct(User $seller)
    {
        $this->seller = $seller;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $userMailer = new UserMailer();
        $userMailer->sellerReviewNotification($this->seller);
    }
}
