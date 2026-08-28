<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'business_id',
        'product_id',
        'income_id',
        'created_by',
        'type',
        'quantity_change',
        'stock_before',
        'stock_after',
        'unit_price',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'quantity_change' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
            'unit_price' => 'decimal:2',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function income()
    {
        return $this->belongsTo(Income::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

