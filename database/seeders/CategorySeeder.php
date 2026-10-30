<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Income
            ['name' => 'Salary', 'type' => 'income', 'color' => '#10B981'],
            ['name' => 'Freelance Income', 'type' => 'income', 'color' => '#059669'],
            ['name' => 'Gifts', 'type' => 'income', 'color' => '#047857'],
            ['name' => 'Other Income', 'type' => 'income', 'color' => '#064E3B'],

            // Expenses
            ['name' => 'Groceries', 'type' => 'expense', 'color' => '#EF4444'],
            ['name' => 'Dining Out', 'type' => 'expense', 'color' => '#DC2626'],
            ['name' => 'Transportation', 'type' => 'expense', 'color' => '#B91C1C'],
            ['name' => 'Shopping', 'type' => 'expense', 'color' => '#BE123C'],
            ['name' => 'Rent and Housing', 'type' => 'expense', 'color' => '#7F1D1D'],
            ['name' => 'Utilities', 'type' => 'expense', 'color' => '#F59E0B'],
            ['name' => 'Internet and Phone', 'type' => 'expense', 'color' => '#D97706'],
            ['name' => 'Healthcare', 'type' => 'expense', 'color' => '#7C3AED'],
            ['name' => 'Entertainment', 'type' => 'expense', 'color' => '#2563EB'],
            ['name' => 'Other Expenses', 'type' => 'expense', 'color' => '#6B7280'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
