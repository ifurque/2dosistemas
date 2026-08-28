<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = ['business_id', 'client_id', 'subject', 'message', 'status', 'answered_at'];
    public function client() { return $this->belongsTo(User::class, 'client_id'); }
}