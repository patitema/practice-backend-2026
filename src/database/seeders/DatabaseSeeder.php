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
        // Roles
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'author'],
            ['id' => 2, 'name' => 'respondent'],
        ]);

        // Statuses
        DB::table('statuses')->insert([
            ['id' => 1, 'name' => 'draft'],
            ['id' => 2, 'name' => 'published'],
            ['id' => 3, 'name' => 'closed'],
        ]);

        // Types
        DB::table('types')->insert([
            ['id' => 1, 'name' => 'single_choice'],
            ['id' => 2, 'name' => 'multiple_choice'],
            ['id' => 3, 'name' => 'text_answer'],
        ]);
    }
}
