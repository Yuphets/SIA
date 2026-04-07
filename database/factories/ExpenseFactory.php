<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    public function definition(): array
    {
        $categories = ['Food', 'Transport', 'Utilities', 'Entertainment', 'Others'];
        return [
            'user_id' => User::factory(),
            'expense_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'category' => fake()->randomElement($categories),
            'description' => fake()->sentence(3),
            'amount' => fake()->randomFloat(2, 50, 5000),
        ];
    }
}
