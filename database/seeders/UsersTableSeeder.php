<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\facades\Hash;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::truncate();
        DB::table('role_user')->truncate();

        // Get all roles
        $adminRole = Role::where('name', 'admin')->first();
        $supplyRole = Role::where('name', 'supply')->first();
        $inspectorRole = Role::where('name', 'inspector')->first();

        // Admin
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@mail.com',
            'password' => Hash::make('admin')
        ]);
        $admin->roles()->attach($adminRole);

        // Supply
        $supplyUser = User::create([
            'name' => 'Supply User',
            'email' => 'supply@gmail.com',
            'password' => Hash::make('1234')
        ]);
        $supplyUser->roles()->attach($supplyRole);

        // Inspector
        $inspectorUser = User::create([
            'name' => 'Inspector',
            'email' => 'inspect@mail.com',
            'password' => Hash::make('user')
        ]);
        $inspectorUser->roles()->attach($inspectorRole);
    }
}
