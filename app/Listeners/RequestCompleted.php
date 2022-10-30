<?php

namespace MotionArray\Listeners;

use MotionArray\Events\SubmissionApproved;
use MotionArray\Mailers\RequestMailer;

class RequestCompleted
{
    protected $requestsMailer;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(RequestMailer $requestMailer)
    {
        $this->requestsMailer = $requestMailer;
    }

    /**
     * Handle the event.
     *
     * @param  SubmissionApproved $event
     *
     * @return void
     */
    public function handle(SubmissionApproved $event)
    {
        $product = $event->submission->product;

        $request = $product->request;

        if ($request) {
            $request->changeStatus('complete');

            $this->requestsMailer->requestProduct($request, $product);
        }
    }
}
