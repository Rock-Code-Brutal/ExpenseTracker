<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Transaction;
use Brick\Math\BigDecimal;
use Illuminate\Support\Collection;

class BudgetSpending
{
    /** Add fresh totals without storing derived values or issuing a query per budget. */
    public function enrich(Collection $budgets): Collection
    {
        foreach ($budgets->groupBy(fn (Budget $budget) => $budget->month->format('Y-m').'|'.$budget->currency) as $group) {
            $first = $group->first();
            $start = $first->month->copy()->startOfMonth();
            $totals = [];

            $transactions = Transaction::query()
                ->whereIn('category_id', $group->pluck('category_id'))
                ->where('currency', $first->currency)
                ->where('type', 'expense')
                ->where('transaction_date', '>=', $start->toDateString())
                ->where('transaction_date', '<', $start->copy()->addMonth()->toDateString())
                ->select(['category_id', 'amount'])
                ->cursor();

            foreach ($transactions as $transaction) {
                $key = $transaction->category_id;
                $totals[$key] = ($totals[$key] ?? BigDecimal::of('0.00'))->plus($transaction->amount);
            }

            foreach ($group as $budget) {
                $spent = $totals[$budget->category_id] ?? BigDecimal::of('0.00');
                $remaining = BigDecimal::of($budget->amount)->minus($spent);
                $budget->setAttribute('actual_spending', (string) $spent->toScale(2));
                $budget->setAttribute('remaining', (string) $remaining->toScale(2));
                $budget->setAttribute('is_over_budget', $remaining->isNegative());
            }
        }

        return $budgets;
    }
}
