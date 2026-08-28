<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = ['business_id', 'user_id', 'reviewer_name', 'rating', 'body', 'is_visible'];
    protected function casts(): array { return ['rating' => 'integer', 'is_visible' => 'boolean']; }
    public function user() { return $this->belongsTo(User::class); }
}