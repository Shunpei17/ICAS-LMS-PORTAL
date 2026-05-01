<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // This is the trigger that runs your UserSeeder code
        $this->call([
            UserSeeder::class,
            AdminDemoSeeder::class,
        ]);
    }
}
