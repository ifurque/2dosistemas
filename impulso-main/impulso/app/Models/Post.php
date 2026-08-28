<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $fillable = ['business_id', 'created_by', 'title', 'body', 'photo', 'type', 'price', 'discount_price', 'starts_at', 'ends_at', 'is_published'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2', 'discount_price' => 'decimal:2', 'starts_at' => 'date', 'ends_at' => 'date', 'is_published' => 'boolean'];
    }

    public function discountPercentage(): ?int
    {
        if (! $this->price || ! $this->discount_price || (float) $this->price <= 0) {
            return null;
        }

        return (int) round((1 - ((float) $this->discount_price / (float) $this->price)) * 100);
    }
}