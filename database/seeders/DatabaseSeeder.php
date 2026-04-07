<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Expense;


class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@materdei.edu.ph',
            'password' => Hash::make('admin'),
            'role' => 'admin',
            'budget_limit' => 20000,
        ]);

        // Create regular users
        User::create([
            'name' => 'John Doe',
            'email' => 'john@materdei.edu.ph',
            'password' => Hash::make('user'),
            'role' => 'user',
            'budget_limit' => 10000,
        ]);

        User::create([
            'name' => 'Jane Smith',
            'email' => 'jane@materdei.edu.ph',
            'password' => Hash::make('user'),
            'role' => 'user',
            'budget_limit' => 8000,
        ]);


        // Create 20 additional random users
        User::factory(20)->create();

        User::all()->each(function ($user) {
    // Create 5-15 random expenses per user
    Expense::factory(fake()->numberBetween(5, 15))->create(['user_id' => $user->id]);
});


    }
}
