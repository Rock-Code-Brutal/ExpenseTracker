<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $currency = $request->get('currency', 'IDR');
        $currentMonth = Carbon::now()->format('Y-m');

        //Total Balance
        $totalIncome = Transaction::where('type', 'income')
            ->where('currency', $currency)
            ->sum('amount');

        $totalExpense = Transaction::where('type', 'expense')
            ->where('currency', $currency)
            ->sum('amount');

        $balance = $totalIncome - $totalExpense;

        //Statistik Bulan Ini  
        $monthlyIncome = Transaction::where('type', 'income')
            ->where('currency', $currency)
            ->whereRaw("strftime('%Y-%m', transaction_date) = ?", [$currentMonth])
            ->sum('amount');

        $monthlyExpense = Transaction::where('type', 'expense')
            ->where('currency', $currency)
            ->whereRaw("strftime('%Y-%m', transaction_date) = ?", [$currentMonth])
            ->sum('amount');

        //Data Bulanan Untuk Bagan (Terbaru 5)
        $recentTransactions = Transaction::with('category')
            ->where('currency', $currency)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        //Data Bulanan untuk Bagan 6 Bulan Terakhir
        $monthlyData = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i)->format('Y-m');
            $monthName = Carbon::now()->subMonths($i)->format('M Y');

            $income = Transaction::where('type', 'income')
                ->where('currency', $currency)
                ->whereRaw("strftime('%Y-%m', transaction_date) = ?", [$month])
                ->sum('amount');

            $expense = Transaction::where('type', 'expense')
                ->where('currency', $currency)
                ->whereRaw("strftime('%Y-%m', transaction_date) = ?", [$month])
                ->sum('amount');

            $monthlyData[] = [
                'month' => $monthName,
                'income' => $income,
                'expense' => $expense,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $balance,
                'total_income' => $totalIncome,
                'total_expense' => $totalExpense,
                'monthly_income' => $monthlyIncome,
                'monthly_expense' => $monthlyExpense,
                'monthly_balance' => $monthlyIncome - $monthlyExpense,
                'recent_transactions' => $recentTransactions,
                'monthly_data' => $monthlyData,
                'currency' => $currency,
            ]
        ]);
    }
}
