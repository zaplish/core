<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Mail;

class SendMail
{
    /**
     * Reset password mail
     */
    static function resetPassword($data)
    {
        $recipient = $data['user']->email;

        $mailData = [
            'template' => 'admin::email.reset-password',
            'subject' => __('admin::mail.resetPassword.subject'),
            'data' => $data
        ];

        Mail::to($recipient)->send(new \App\Mail\Mail($mailData));
    }
}
