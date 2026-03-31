<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@materdei.edu.ph',
            'password' => bcrypt('admin'),
            'role' => 'admin',
            'budget_limit' => 20000,
        ]);

        // Create regular users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@materdei.edu.ph',
            'password' => bcrypt('user'),
            'role' => 'user',
            'budget_limit' => 10000,
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@materdei.edu.ph',
            'password' => bcrypt('user'),
            'role' => 'user',
            'budget_limit' => 8000,
        ]);
    }
}
