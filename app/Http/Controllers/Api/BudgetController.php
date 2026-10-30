<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use App\Services\BudgetSpending;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BudgetController extends Controller
{
    public function __construct(private readonly BudgetSpending $spending) {}

    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'currency' => ['required', Rule::in(['USD', 'CAD'])],
        ]);

        $budgets = Budget::with('category')
            ->where('month', $validated['month'].'-01')
            ->where('currency', $validated['currency'])
            ->orderBy('category_id')
            ->get();

        return response()->json(['success' => true, 'data' => $this->spending->enrich($budgets)]);
    }

    public function store(Request $request): JsonResponse
    {
        $attributes = $this->validatedAttributes($request);

        try {
            $budget = Budget::create($attributes);
        } catch (UniqueConstraintViolationException $exception) {
            $this->duplicateBudget();
        }

        return response()->json([
            'success' => true,
            'message' => 'Budget created successfully',
            'data' => $this->spending->enrich(collect([$budget->load('category')]))->first(),
        ], 201);
    }

    public function show(Budget $budget): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->spending->enrich(collect([$budget->load('category')]))->first()]);
    }

    public function update(Request $request, Budget $budget): JsonResponse
    {
        $attributes = $this->validatedAttributes($request, $budget);

        try {
            $budget->update($attributes);
        } catch (UniqueConstraintViolationException $exception) {
            $this->duplicateBudget();
        }

        return response()->json([
            'success' => true,
            'message' => 'Budget updated successfully',
            'data' => $this->spending->enrich(collect([$budget->load('category')]))->first(),
        ]);
    }

    public function destroy(Budget $budget): JsonResponse
    {
        $budget->delete();

        return response()->json(['success' => true, 'message' => 'Budget deleted successfully']);
    }

    private function validatedAttributes(Request $request, ?Budget $budget = null): array
    {
        $attributes = $request->validate([
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('type', 'expense')],
            'month' => ['required', 'date_format:Y-m'],
            'currency' => ['required', Rule::in(['USD', 'CAD'])],
            'amount' => ['required', 'numeric', 'decimal:0,2', 'min:0.01', 'max:9999999999999.99'],
        ]);

        $attributes['month'] .= '-01';
        $duplicate = Budget::where('category_id', $attributes['category_id'])
            ->where('month', $attributes['month'])
            ->where('currency', $attributes['currency']);

        if ($budget !== null) {
            $duplicate->whereKeyNot($budget->getKey());
        }

        if ($duplicate->exists()) {
            $this->duplicateBudget();
        }

        return $attributes;
    }

    private function duplicateBudget(): never
    {
        throw ValidationException::withMessages([
            'category_id' => 'A budget already exists for this category, month, and currency.',
        ]);
    }
}
