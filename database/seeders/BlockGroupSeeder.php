<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class BlockGroupSeeder extends Seeder
{
    public function run()
    {
        $now = now();

        $developer = \App\Models\User::where('role', 'developer')->orderBy('id')->first();
        $developerId = $developer?->id ?? null;

        $groups = [
            [
                'key' => 'standard',
                'label' => 'Standard',
                'order' => 1,
                'settings' => json_encode([]),
                'created_by' => $developerId,
                'updated_by' => $developerId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'media',
                'label' => 'Media',
                'order' => 2,
                'settings' => json_encode([]),
                'created_by' => $developerId,
                'updated_by' => $developerId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'layout',
                'label' => 'Layout',
                'order' => 3,
                'settings' => json_encode([]),
                'created_by' => $developerId,
                'updated_by' => $developerId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'dynamic',
                'label' => 'Dynamic',
                'order' => 4,
                'settings' => json_encode([]),
                'created_by' => $developerId,
                'updated_by' => $developerId,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($groups as $group) {
            DB::table('block_groups')->updateOrInsert(
                ['key' => $group['key']],
                $group
            );
        }
    }
}

