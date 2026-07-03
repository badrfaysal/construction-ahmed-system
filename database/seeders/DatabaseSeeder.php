<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Intentionally empty: login is now shared with the first system's `users`
     * table (see App\Models\User). Seeding a user here would insert a row into
     * that other business's live table — never do that. User accounts are
     * managed exclusively from the first system.
     */
    public function run(): void
    {
        //
    }
}
