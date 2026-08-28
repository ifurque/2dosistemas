<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['business_id', 'name', 'description', 'photo', 'type', 'price', 'duration', 'is_active', 'stock'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'is_active' => 'boolean', 'stock' => 'integer'];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
