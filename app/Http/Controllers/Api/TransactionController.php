<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with('category');

        //Filter dari tipe
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        //Filter dari Kurensi
        if ($request->has('currency')) {
            $query->where('currency', $request->currency);
        }

        //Filter dari tanggal
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        //Filter dari Kategori
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
                            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:IDR,USD',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $transaction = Transaction::create($validated);
        $transaction->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil ditambahkan',
            'data' => $transaction,
        ],201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load('category');
        return response()->json([
            'success' => true,
            'data' => $transaction,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|in:IDR,USD',
            'type' => 'required|in:income,expense',
            'description' => 'nullable|string',
            'transaction_date' => 'required|date',
        ]);

        $transaction->update($validated);
        $transaction->load('category');

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil diubah',
            'data' => $transaction,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $transaction->delete();

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil dihapus',
        ]);
    }

    /**
     * Import transactions from CSV
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'csv_data' => 'required|string',
            'currency' => 'required|in:IDR,USD',
        ]);

        $csvData = $request->csv_data;
        $currency = $request->currency;
        $lines = str_getcsv($csvData, "\n");
        
        if (count($lines) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'CSV file must contain at least a header and one data row',
            ], 422);
        }

        // Parse header
        $header = str_getcsv($lines[0]);
        $expectedHeaders = ['Date', 'Type', 'Category', 'Amount', 'Currency', 'Description'];
        
        if ($header !== $expectedHeaders) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid CSV format. Expected headers: ' . implode(', ', $expectedHeaders),
                'expected' => $expectedHeaders,
                'received' => $header,
            ], 422);
        }

        $imported = 0;
        $errors = [];
        $categories = \App\Models\Category::pluck('name', 'id')->toArray();
        $categoryMap = array_flip($categories); // name -> id mapping

        // Process data rows
        for ($i = 1; $i < count($lines); $i++) {
            $row = str_getcsv($lines[$i]);
            
            if (count($row) !== 6) {
                $errors[] = "Row {$i}: Invalid number of columns";
                continue;
            }

            [$date, $type, $categoryName, $amount, $csvCurrency, $description] = $row;

            // Validate and find category
            if (!isset($categoryMap[$categoryName])) {
                $errors[] = "Row {$i}: Category '{$categoryName}' not found";
                continue;
            }

            try {
                Transaction::create([
                    'transaction_date' => $date,
                    'type' => $type,
                    'category_id' => $categoryMap[$categoryName],
                    'amount' => (float) $amount,
                    'currency' => $currency, // Use selected currency
                    'description' => $description ?: null,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row {$i}: {$e->getMessage()}";
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully imported {$imported} transactions",
            'imported' => $imported,
            'errors' => $errors,
        ]);
    }
}
