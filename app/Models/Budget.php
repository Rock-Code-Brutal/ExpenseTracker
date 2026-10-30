<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    protected $fillable = [
        'category_id',
        'month',
        'currency',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'month' => 'date:Y-m-d',
            'amount' => 'decimal:2',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
