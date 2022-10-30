<?php namespace MotionArray\Listeners;

use Carbon\Carbon;
use MotionArray\Facades\Slack;

class CheckForSuspiciousActivity
{
    protected $details;

    protected $message;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  SubscriptionUpgraded $event
     *
     * @return void
     */
    public function handle($event)
    {
        $user = $event->user;

        $sendAlerts = \Config::get('services.slack.send_alerts');

        if (!$sendAlerts || $user->isFreeloader()) {
            return;
        }

        $this->details = "User Email: " . $user->email . "\n" .
            config(app.url) . "/mabackend/user-manager/search/results?q=" . $user->email . " \n";

        $this->message = null;

        $this->checkReapetedUpgrade($user);

        if ($this->message) {
            Slack::attach([
                'text' => $this->details,
                'color' => 'danger',
            ])->send('Suspicious activity Alert: ' . $this->message);
        }
    }

    /**
     * @param $event
     * @param $user
     */
    public function checkReapetedUpgrade($user)
    {
        $payments = $user->payments()
            ->where('created_at', '>', Carbon::now()->subHours(24))
            ->get();

        if ($payments->count() > 1) {
            $this->message = 'Repeated Upgrade';
        }
    }
}