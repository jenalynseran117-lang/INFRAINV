<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
// Idagdag ang Schema facade dito sa taas
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Patayin ang Foreign Key Checks
        Schema::disableForeignKeyConstraints();

        // 2. I-run ang iyong mga seeders
        $this->call(RolesTableSeeder::class);
        $this->call(UsersTableSeeder::class);

        // 3. Buksan ulit ang Foreign Key Checks
        Schema::enableForeignKeyConstraints();
    }
}
