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
            'category_id' => 'exists:categories,id',
            'amount' => 'numeric|min:0.01',
            'currency' => 'in:IDR,USD',
            'type' => 'in:income,expense',
            'description' => 'nullable|string',
            'transaction_date' => 'date',
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
}
