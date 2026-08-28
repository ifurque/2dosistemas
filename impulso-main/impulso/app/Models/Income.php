<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Income extends Model
{
    protected $fillable = ['business_id', 'created_by', 'product_id', 'description', 'amount', 'income_date', 'source', 'items'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'income_date' => 'date', 'items' => 'array'];
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
