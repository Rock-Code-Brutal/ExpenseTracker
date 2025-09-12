<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'amount',
        'currency',
        'type',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'category_id' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpenses($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeCurrency($query, $currency)
    {
        return $query->where('currency', $currency);
    }

    public function scopeDataRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }
}
