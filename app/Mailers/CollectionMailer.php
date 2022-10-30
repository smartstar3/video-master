<?php namespace MotionArray\Mailers;

class CollectionMailer extends Mailer
{

    public function shareCollection($url, $sender, $recipient)
    {
        /**
         * Email variables
         * The view uses hardcoded url due to the redirect for /browse
         */
        $view = "site.emails.collection.share-collection";
        $data = [
            "url" => $url,
            "sender" => $sender,
            "recipient" => $recipient
        ];

        $subject = "Hey! " . $sender->firstname . " has shared some awesome products with you.";

        /**
         * Send mail
         */
        return $this->sendTo($recipient, $subject, $view, $data);
    }
}
