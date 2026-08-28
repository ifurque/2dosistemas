<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id', 'name', 'slug', 'category', 'description', 'location',
        'phone', 'email', 'logo', 'profile_photo', 'cover_photo', 'opening_hours', 'is_public',
    ];

    protected function casts(): array
    {
        return ['opening_hours' => 'array', 'is_public' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (Business $business) {
            $business->slug ??= Str::slug($business->name).'-'.Str::lower(Str::random(5));
        });
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'business_members')
            ->withPivot(['role', 'permissions', 'joined_at'])->withTimestamps();
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function inquiries()
    {
        return $this->hasMany(Inquiry::class);
    }

    public function socialLinks()
    {
        return $this->hasMany(SocialLink::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function availabilityHours()
    {
        return $this->hasMany(BusinessAvailabilityHours::class);
    }
}
