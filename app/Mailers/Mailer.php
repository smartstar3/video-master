<?php namespace MotionArray\Mailers;

use Illuminate\Mail\Message;
use Mail;

abstract class Mailer
{
    protected $from;

    public function sendTo($to, $subject, $view, $data = [], $from = null, $reply = null)
    {
        if (is_array($to)) {
            $to = array_unique($to);

            $response = true;

            foreach ($to as $recipient) {
                if ($recipient) {
                    $response = $response && $this->send($recipient, $subject, $view, $data, $from, $reply);
                }
            }

            return $response;
        } else {
            return $this->send($to, $subject, $view, $data, $from, $reply);
        }
    }

    public function send($to, $subject, $view, $data, $from, $reply)
    {
        if (!$from && isset($this->from) && $this->from) {
            $from = $this->from;
        }

        Mail::send($view, $data, function (Message $message) use ($to, $subject, $from, $reply) {
            $message->to($to)->subject($subject);

            if (is_array($from) && $from["email"]) {
                $message->from($from["email"], $from["name"]);

                if (isset($from["replyTo"])) {
                    $message->replyTo($from["replyTo"], $from["name"]);
                }
            } elseif ($from) {
                $message->from($from);
            }

            if ($reply) {
                $message->replyTo($reply);
            }
        });

        if(is_array(Mail::failures()) && count(Mail::failures()) > 0){
            return false;
        }

        return true;
    }

}