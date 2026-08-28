<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = ['business_id', 'client_id', 'product_id', 'guest_name', 'guest_email', 'appointment_date', 'start_time', 'end_time', 'status', 'confirmation_token', 'confirmed_at', 'notes'];
    protected function casts(): array { return ['appointment_date' => 'date', 'confirmed_at' => 'datetime']; }
    public function client() { return $this->belongsTo(User::class, 'client_id'); }
    public function product() { return $this->belongsTo(Product::class); }
    public function business() { return $this->belongsTo(Business::class); }
}