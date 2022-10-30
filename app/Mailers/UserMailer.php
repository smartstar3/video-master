<?php namespace MotionArray\Mailers;

use Illuminate\Auth\Passwords\TokenRepositoryInterface;
use MotionArray\Models\Portfolio;
use MotionArray\Models\User;

class UserMailer extends Mailer
{
    public function confirmation(User $user, $from = null)
    {
        if (!$user->confirmed) {
            /**
             * Set confirmation code
             */
            $confirmationToken = $user->createConfirmationToken();

            $view = "site.emails.user.confirmation";
            $data = [
                "user" => $user,
                "confirmation_code" => $confirmationToken
            ];
            $subject = "Confirm your email address";

            return $this->sendTo($user->email, $subject, $view, $data, $from);
        }
    }

    public function welcome(User $user, $from = null)
    {
        /**
         * Set confirmation code
         */
        $confirmationToken = $user->createConfirmationToken();

        $view = "site.emails.user.welcome";
        $data = [
            "user" => $user,
            "firstname" => $user->firstname,
            "confirmation_code" => $confirmationToken
        ];
        $subject = "Welcome to Motion Array";

        return $this->sendTo($user->email, $subject, $view, $data, $from);
    }

    public function importantChange(User $user, $change = 'password')
    {
        $view = "site.emails.user.important-change";

        $data = [
            "firstname" => $user->firstname,
            "date" => $user->updated_at,
            "change" => $change
        ];

        $subject = "Your " . $change . " was changed";

        $original = $user->getOriginal();

        return $this->sendTo($original['email'], $subject, $view, $data);
    }

    public function upgraded(User $user, $from = null)
    {
        $view = "site.emails.user.upgrade";
        $data = [
            "user" => $user
        ];
        $subject = "Congratulations on your membership upgrade!";

        return $this->sendTo($user->email, $subject, $view, $data, $from);
    }

    public function invoicePaymentFailed(User $user, $from = null)
    {
        $view = "site.emails.user.invoice-payment-failed";
        $data = [
            "user" => $user
        ];
        $subject = "Oops. We couldn’t collect your payment.";

        return $this->sendTo($user->email, $subject, $view, $data, $from);
    }

    public function accountDowngraded(User $user, $from = null)
    {
        $view = "site.emails.user.account-downgraded";
        $data = [
            "user" => $user
        ];
        $subject = "You’ve been downgraded.";

        return $this->sendTo($user->email, $subject, $view, $data, $from);
    }

    public function portfolioMessage($inputs)
    {
        $to = $inputs["receive_email"];

        $view = "site.emails.user.portfolio";

        $data = [
            "name" => $inputs["name"],
            "email" => $inputs["email"],
            "body" => strip_tags(nl2br($inputs["message"]), '<br>')
        ];

        $from = [
            'email' => 'no-reply@motionarray.com',
            'name' => "Motion Array"
        ];

        $reply = $inputs["email"];

        $subject = 'MotionArray Portfolio Message';

        return $this->sendTo($to, $subject, $view, $data, $from, $reply);
    }

    public function restoreAccount(User $user)
    {
        $token = app('auth.password.broker')->createToken($user);

        $view = "site.emails.user.restore-account";

        $data = [
            "user" => $user,
            'token' => $token
        ];

        $from = [
            'email' => 'no-reply@motionarray.com',
            'name' => "Motion Array"
        ];

        $subject = "Restore your Motion Array account";

        return $this->sendTo($user->email, $subject, $view, $data, $from);
    }

    public function contentRemovalWarning(User $user, $days)
    {
        $view = "site.emails.user.content-removal-warning";

        $data = [
            "user" => $user,
            "days" => $days
        ];

        $from = [
            'email' => 'no-reply@motionarray.com',
            'name' => "Motion Array"
        ];

        $subject = 'Warning: Your video uploads will be deleted soon.';

        return $this->sendTo($user->email, $subject, $view, $data, $from);
    }

    public function sellerReviewNotification(User $user)
    {
        $view = "site.emails.user.seller-review-notification";

        $data = [
            "user" => $user
        ];

        $from = [
            'email' => 'no-reply@motionarray.com',
            'name' => "Motion Array"
        ];

        $subject = 'Someone reviewed your work!';

        return $this->sendTo($user->email, $subject, $view, $data, $from);
    }
}
