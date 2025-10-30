<?php

namespace App\Helpers;

use App\Models\User;

class ValidateHelper
{
    // Validate a users name

    static function userName($value = null)
    {
        // Clean up
        $value = StringHelper::removeSpaces($value);

        // Not empty
        if (empty($value)) {
            return 'nameEmpty';
        }
        // At least 3 chars
        if (strlen($value) < 3) {
            return 'nameMin';
        }
        // Not more than 30 chars
        if (strlen($value) > 30) {
            return 'nameMax';
        }
        // Only letters and numbers
        if (preg_match('/[^\p{L}0-9\- ]/ui', $value)) {
            return 'nameAlphaNum';
        }
        return true;
    }

    // Validate email

    static function userEmail($value = null)
    {
        // Clean up
        $value = StringHelper::removeSpaces($value);

        // Not empty
        if (empty($value)) {
            return 'emailEmpty';
        }
        // PHP email validation
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'emailInvalid';
        }
        // Check if email is registered
        if (User::where(['email' => $value])->count() > 0) {
            return 'emailRegistered';
        }
        return true;
    }

    // Validate password

    static function userPassword($value = null)
    {
        // Not empty
        if (empty($value)) {
            return 'passwordEmpty';
        }
        // At least 8 chars
        if (strlen($value) < 8) {
            return 'passwordMin';
        }
        // Not more than 50 chars
        if (strlen($value) > 50) {
            return 'passwordMax';
        }
        return true;
    }
}
