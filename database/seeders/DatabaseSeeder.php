<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Database\Seeders\SubUserSeeder;
use Database\Seeders\SlaConfigSeeder;
use Database\Seeders\MasterLiburNasionalSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            SubUserSeeder::class,
            SlaConfigSeeder::class,
            MasterLiburNasionalSeeder::class,
        ]);
    }
}
