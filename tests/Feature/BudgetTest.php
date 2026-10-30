<?php

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('connects budgets to their category', function () {
    $category = Category::create([
        'name' => 'Groceries',
        'type' => 'expense',
        'color' => '#10B981',
    ]);

    $budget = $category->budgets()->create([
        'month' => '2026-10-01',
        'currency' => 'USD',
        'amount' => '500.00',
    ]);

    expect($budget->category->is($category))->toBeTrue()
        ->and($category->budgets->first()->is($budget))->toBeTrue()
        ->and($budget->amount)->toBe('500.00');
});

it('rejects duplicate category month and currency budgets', function () {
    $category = Category::create([
        'name' => 'Groceries',
        'type' => 'expense',
        'color' => '#10B981',
    ]);

    $attributes = [
        'category_id' => $category->id,
        'month' => '2026-10-01',
        'currency' => 'USD',
        'amount' => '500.00',
    ];

    Budget::create($attributes);

    expect(fn () => Budget::create($attributes))
        ->toThrow(QueryException::class);
});