<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BusinessController extends Controller
{
    public function create() { return view('business.create'); }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'category' => ['required', 'string', 'max:80'],
            'description' => ['required', 'string', 'max:1000'],
            'location' => ['required', 'string', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:180'],
            'profile_photo' => ['nullable', 'image', 'max:5120'],
            'cover_photo' => ['nullable', 'image', 'max:8192'],
        ]);
        foreach (['profile_photo', 'cover_photo'] as $image) {
            if ($request->hasFile($image)) {
                $data[$image] = $request->file($image)->store('businesses', 'public');
            }
        }
        $business = $request->user()->ownedBusinesses()->create($data + ['slug' => Str::slug($data['name']).'-'.Str::random(5)]);
        $business->members()->attach($request->user()->id, ['role' => 'owner', 'joined_at' => now()]);
        $request->user()->update(['is_entrepreneur' => true]);
        ExpenseCategory::query()->insertOrIgnore([
            ['name' => 'Materia prima', 'slug' => 'materia-prima', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sueldos', 'slug' => 'sueldos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Bienes y equipamiento', 'slug' => 'bienes-equipamiento', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Servicios', 'slug' => 'servicios', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Alquiler', 'slug' => 'alquiler', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Otros', 'slug' => 'otros', 'created_at' => now(), 'updated_at' => now()],
        ]);
        return redirect()->route('dashboard', $business)->with('success', 'Tu emprendimiento fue creado.');
    }

    public function show(Business $business)
    {
        abort_unless($business->is_public, 404);
        return view('business.show', ['business' => $business->load(['products', 'socialLinks', 'reviews' => fn ($query) => $query->where('is_visible', true)->latest(), 'posts' => fn ($query) => $query->where('is_published', true)->where(fn ($query) => $query->whereNull('starts_at')->orWhereDate('starts_at', '<=', today()))->where(fn ($query) => $query->whereNull('ends_at')->orWhereDate('ends_at', '>=', today()))])]);
    }
}