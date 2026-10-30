<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = Category::create(['name' => 'Groceries', 'type' => 'expense', 'color' => '#10B981']);
    $this->payload = ['category_id' => $this->category->id, 'month' => '2026-10', 'currency' => 'USD', 'amount' => '500.00'];
});

it('creates shows and updates a budget while normalizing its month', function () {
    $id = $this->postJson('/api/budgets', $this->payload)->assertCreated()
        ->assertJsonPath('data.month', '2026-10-01')->assertJsonPath('data.category.name', 'Groceries')->json('data.id');
    $this->getJson("/api/budgets/$id")->assertOk();
    $this->putJson("/api/budgets/$id", [...$this->payload, 'amount' => '600.50'])
        ->assertOk()->assertJsonPath('data.amount', '600.50');
});

it('rejects duplicate creation and conflicting updates', function () {
    $this->postJson('/api/budgets', $this->payload)->assertCreated();
    $this->postJson('/api/budgets', $this->payload)->assertUnprocessable()->assertJsonValidationErrors('category_id');
    $id = $this->postJson('/api/budgets', [...$this->payload, 'currency' => 'CAD'])->assertCreated()->json('data.id');
    $this->putJson("/api/budgets/$id", $this->payload)->assertUnprocessable()->assertJsonValidationErrors('category_id');
});

it('filters budgets by both month and currency', function () {
    foreach ([['2026-10', 'USD'], ['2026-11', 'USD'], ['2026-10', 'CAD']] as [$month, $currency]) {
        $this->postJson('/api/budgets', [...$this->payload, 'month' => $month, 'currency' => $currency])->assertCreated();
    }
    $this->getJson('/api/budgets?month=2026-10&currency=USD')->assertOk()
        ->assertJsonCount(1, 'data')->assertJsonPath('data.0.currency', 'USD')->assertJsonPath('data.0.month', '2026-10-01');
    $this->getJson('/api/budgets')->assertUnprocessable()->assertJsonValidationErrors(['month', 'currency']);
});

it('validates budget inputs', function ($field, $value) {
    $this->postJson('/api/budgets', [...$this->payload, $field => $value])
        ->assertUnprocessable()->assertJsonValidationErrors($field);
})->with([
    ['amount', 0], ['amount', -1], ['amount', '12.345'], ['amount', 'abc'],
    ['currency', 'IDR'], ['month', '2026-13'], ['month', '2026-10-15'], ['category_id', 9999],
]);

it('rejects income categories', function () {
    $this->category->update(['type' => 'income']);
    $this->postJson('/api/budgets', $this->payload)->assertUnprocessable()->assertJsonValidationErrors('category_id');
});

it('deletes only the budget and returns 404 for missing budgets', function () {
    $transaction = Transaction::create(['category_id' => $this->category->id, 'amount' => '25.00', 'currency' => 'USD', 'type' => 'expense', 'transaction_date' => '2026-10-15']);
    $id = $this->postJson('/api/budgets', $this->payload)->assertCreated()->json('data.id');
    $this->deleteJson("/api/budgets/$id")->assertOk();
    expect(Budget::count())->toBe(0);
    $this->assertDatabaseHas('categories', ['id' => $this->category->id]);
    $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
    $this->getJson("/api/budgets/$id")->assertNotFound();
});
