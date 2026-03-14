<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles - use insertOrIgnore to avoid duplicates
        DB::table('roles')->insertOrIgnore([
            ['id' => 1, 'name' => 'author'],
            ['id' => 2, 'name' => 'respondent'],
            ['id' => 3, 'name' => 'admin'],
        ]);

        // Statuses
        DB::table('statuses')->insertOrIgnore([
            ['id' => 1, 'name' => 'draft'],
            ['id' => 2, 'name' => 'published'],
            ['id' => 3, 'name' => 'closed'],
        ]);

        // Types
        DB::table('types')->insertOrIgnore([
            ['id' => 1, 'name' => 'single_choice'],
            ['id' => 2, 'name' => 'multiple_choice'],
            ['id' => 3, 'name' => 'text_answer'],
        ]);
    }
}
