<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    /**
     * Setup before each test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Seed reference data for tests
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'author'],
            ['id' => 2, 'name' => 'respondent'],
            ['id' => 3, 'name' => 'admin'],
        ]);

        DB::table('statuses')->insert([
            ['id' => 1, 'name' => 'draft'],
            ['id' => 2, 'name' => 'published'],
            ['id' => 3, 'name' => 'closed'],
        ]);

        DB::table('types')->insert([
            ['id' => 1, 'name' => 'single_choice'],
            ['id' => 2, 'name' => 'multiple_choice'],
            ['id' => 3, 'name' => 'text_answer'],
        ]);
    }
}
