<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Business $business)
    {
        $data = $request->validate([
            'reviewer_name' => ['required', 'string', 'max:100'],
            'rating' => ['required', 'integer', 'between:1,5'],
            'body' => ['required', 'string', 'max:1000'],
        ]);
        $business->reviews()->create($data + ['user_id' => $request->user()?->id]);
        return back()->with('success', 'Gracias por compartir tu experiencia.');
    }
}