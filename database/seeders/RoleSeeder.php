<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::firstOrCreate(['name' => User::ROLE_ADMIN]);
        Role::firstOrCreate(['name' => User::ROLE_VENDOR]);
        Role::firstOrCreate(['name' => User::ROLE_CUSTOMER]);

        $admin = User::where('email', 'admin@email.com')->first();
        $admin->assignRole(User::ROLE_ADMIN);
    }
}
