<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Deliberately no accounts: administrators are created one at a time with
     * "php artisan portfolio:make-admin", so no password is ever written into
     * the source code or a seeder.
     */
    public function run(): void
    {
        $this->call(ProjectsSeeder::class);
    }
}
