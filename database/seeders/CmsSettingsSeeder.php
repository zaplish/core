<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsSettingsSeeder extends Seeder
{
    public function run()
    {
        DB::table('settings')->insertOrIgnore([
            [
                'key' => 'zaplish.name',
                'value' => 'Zaplish',
            ],
            [
                'key' => 'zaplish.theme',
                'value' => 'zaplish',
            ],
        ]);
    }
}
