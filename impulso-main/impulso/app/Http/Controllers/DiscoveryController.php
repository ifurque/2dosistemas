<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Http\Request;

class DiscoveryController extends Controller
{
    public function index(Request $request)
    {
        $query = Business::query()->where('is_public', true)
            ->when($request->search, fn ($q, $search) => $q->where(fn ($subq) => $subq->where('name', 'like', "%$search%")->orWhere('description', 'like', "%$search%")))
            ->when($request->category, fn ($q, $category) => $q->where('category', $category));
        
        $businesses = $query->latest()->withCount('products')->with('posts')
            ->paginate($request->ajax() ? 9 : 9)->withQueryString();
        
        $categories = Business::where('is_public', true)->distinct('category')->pluck('category')->sort();
        
        if ($request->ajax()) {
            return response()->json([
                'html' => view('partials.business-grid', compact('businesses'))->render(),
                'pagination' => $businesses->render()
            ]);
        }
        
        return view('discover', compact('businesses', 'categories'));
    }
}