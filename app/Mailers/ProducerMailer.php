<?php namespace MotionArray\Mailers;

use MotionArray\Models\Traits\Uploadable;
use MotionArray\Models\User;

class ProducerMailer extends Mailer
{
    public function encodingComplete(User $producer, Uploadable $uploadable)
    {
        /**
         * Email variables
         */
        $view = "site.emails.producer.encoding-complete";

        $subject = 'Encoding Complete for ' . $uploadable->name;

        $recipients = $producer->email;

        $url = $uploadable->url;

        return $this->sendTo($recipients, $subject, $view, compact('producer', 'uploadable'));
    }

    public function producerContactForm(User $producer, $formData)
    {
        /**
         * Email variables
         */
        $view = "site.emails.producer.contact-producer";

        $from = [
            "name" => $formData["name"],
            'email' => 'no-reply@motionarray.com',
            "replyTo" => $formData["email"]
        ];

        $formData['body'] = $formData['message'];
        $formData['producer'] = $producer;

        $subject = ucfirst(trim($formData["subject"]));

        if (!$subject) {
            $subject = 'Hey there';
        }

        $recipients = $producer->email;

        /**
         * Send mail
         */
        return $this->sendTo($recipients, $subject, $view, $formData, $from);
    }
}