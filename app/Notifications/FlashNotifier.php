<?php namespace MotionArray\Notifications;

use Illuminate\Session\Store;

class FlashNotifier
{

    private $session;

    function __construct(Store $session)
    {
        $this->session = $session;
    }


    public function message($message, $level = 'info', $dismissible = null)
    {
        $this->session->flash('flash_notification.message', $message);
        $this->session->flash('flash_notification.level', $level);

        if (!is_null($dismissible)) {
            $this->session->flash('flash_notification.dismissible', $dismissible);
        }
    }


    public function clear()
    {
        $this->session->forget("flash_notification.message");
        $this->session->forget("flash_notification.level");
        $this->session->forget("flash_notification.dismissible");
    }


    public function success($message, $dismissible = null)
    {
        $this->message($message, 'success', $dismissible);
    }


    public function danger($message, $dismissible = true)
    {
        $this->message($message, 'danger', $dismissible);
    }


    public function info($message, $dismissible = null)
    {
        $this->message($message, 'info', $dismissible);
    }


    public function warning($message, $dismissible = null)
    {
        $this->message($message, 'warning', $dismissible);
    }

}