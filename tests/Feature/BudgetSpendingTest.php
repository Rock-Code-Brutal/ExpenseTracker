<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->category = Category::create(['name' => 'Groceries', 'type' => 'expense', 'color' => '#10B981']);
    $this->budget = Budget::create(['category_id' => $this->category->id, 'month' => '2026-12-01', 'currency' => 'USD', 'amount' => '100.00']);
    $this->transaction = fn (array $overrides = []) => Transaction::create(array_replace([
        'category_id' => $this->category->id, 'amount' => '10.10', 'currency' => 'USD',
        'type' => 'expense', 'transaction_date' => '2026-12-15',
    ], $overrides));
});

it('includes only matching expenses within calendar month and year boundaries', function () {
    ($this->transaction)(['transaction_date' => '2026-12-01']);
    ($this->transaction)(['transaction_date' => '2026-12-31', 'amount' => '20.20']);
    foreach ([['type' => 'income'], ['currency' => 'CAD'], ['transaction_date' => '2026-11-30'], ['transaction_date' => '2027-01-01']] as $overrides) {
        ($this->transaction)($overrides);
    }
    $other = Category::create(['name' => 'Other', 'type' => 'expense', 'color' => '#10B981']);
    ($this->transaction)(['category_id' => $other->id]);
    $this->getJson('/api/budgets?month=2026-12&currency=USD')->assertOk()
        ->assertJsonPath('data.0.actual_spending', '30.30')->assertJsonPath('data.0.remaining', '69.70')
        ->assertJsonPath('data.0.is_over_budget', false);
});

it('returns zero spending for empty budgets and accurate cents for overspending', function () {
    $url = '/api/budgets/'.$this->budget->id;
    $this->getJson($url)->assertJsonPath('data.actual_spending', '0.00')->assertJsonPath('data.remaining', '100.00');
    ($this->transaction)(['amount' => '100.10']);
    ($this->transaction)(['amount' => '0.20']);
    $this->getJson($url)->assertJsonPath('data.actual_spending', '100.30')
        ->assertJsonPath('data.remaining', '-0.30')->assertJsonPath('data.is_over_budget', true);
});

it('recalculates after transaction edits and deletions', function () {
    $transaction = ($this->transaction)();
    $url = '/api/budgets/'.$this->budget->id;
    $this->getJson($url)->assertJsonPath('data.actual_spending', '10.10');
    $transaction->update(['amount' => '100.00']);
    $this->getJson($url)->assertJsonPath('data.remaining', '0.00')->assertJsonPath('data.is_over_budget', false);
    $transaction->delete();
    $this->getJson($url)->assertJsonPath('data.actual_spending', '0.00');
});

it('handles leap day and excludes the next month', function () {
    $this->budget->update(['month' => '2028-02-01']);
    ($this->transaction)(['transaction_date' => '2028-02-29']);
    ($this->transaction)(['transaction_date' => '2028-03-01']);
    $this->getJson('/api/budgets/'.$this->budget->id)->assertJsonPath('data.actual_spending', '10.10');
});
