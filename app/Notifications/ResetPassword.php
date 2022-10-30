<?php namespace MotionArray\Notifications;

use Illuminate\Support\Facades\Lang;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword as LaravelResetPassword;

class ResetPassword extends LaravelResetPassword
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

//        $resetUrl = url(config('app.url').route('password.reset', $this->token, false));

        return (new MailMessage)
            ->subject('Forgotten password reminder')
            ->view('site.emails.auth.reminder', ['token' => $this->token]);
    }
}
