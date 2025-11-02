<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Admin Prefix
    |--------------------------------------------------------------------------
    |
    | The prefix for the admin area.
    |
    */
    'admin_prefix' => env('ZAPLISH_ADMIN_PREFIX', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | Sqids Configuration
    |--------------------------------------------------------------------------
    |
    | A unique, random string used to encode internal IDs (e.g. for short links).
    | Must be at least 16 characters. Do not share or change after installation.
    |
     */
    'sqids_salt' => env('ZAPLISH_SQIDS_SALT'),

    /*
    |--------------------------------------------------------------------------
    | Available locales for admin
    |--------------------------------------------------------------------------
    |
    | List of all locales that the admin area supports
    |
    */

    'available_locales' => [
        'en' => [
            'id' => 'en',
            'name' => 'English',
            'name_local' => 'English',
            'code' => 'EN',
        ],
        'de' => [
            'id' => 'de',
            'name' => 'German',
            'name_local' => 'Deutsch',
            'code' => 'DE',
        ],
    ],
];
