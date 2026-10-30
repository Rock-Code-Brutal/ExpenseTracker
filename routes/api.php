<?php

use App\Http\Controllers\Api\BudgetController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\TransactionController;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::post('/transactions/import', [TransactionController::class, 'import']);

    Route::get('/settings', [SettingController::class, 'index']);
    Route::put('/settings', [SettingController::class, 'update']);

    Route::apiResource('budgets', BudgetController::class);

});
