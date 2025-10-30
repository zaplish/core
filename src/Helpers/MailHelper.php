<?php

namespace App\Helpers;

use App\Http\Middleware\SendMail;
use Sqids\Sqids;

class MailHelper
{
    /**
     * Send the reset password email
     */
    static function resetPassword($user)
    {
        $sqids = new Sqids(config('cms.sqids_salt'), 8);
        $userIdHashed = $sqids->encode([$user->id]);

        $buttonLink = route('admin.new-password', [
            'userId' => $userIdHashed,
            'resetPasswordHash' => $user->password_reset_hash,
        ], true);

        SendMail::resetPassword([
            'user' => $user,
            'buttonLink' => $buttonLink,
        ]);
    }
}
