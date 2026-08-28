<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index(Business $business)
    {
        $this->ownerOnly($business);
        return view('posts.index', ['business' => $business, 'posts' => $business->posts()->latest()->paginate(10)]);
    }

    public function store(Request $request, Business $business)
    {
        $this->ownerOnly($business);
        $data = $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'body' => ['required', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'type' => ['required', 'in:product,offer,news'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:price'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_published' => ['nullable', 'boolean'],
        ]);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('posts', 'public');
        }
        $business->posts()->create($data + ['created_by' => Auth::id(), 'is_published' => $request->boolean('is_published')]);
        return back()->with('success', 'Publicación creada.');
    }

    public function update(Request $request, Business $business, Post $post)
    {
        $this->ownerOnly($business);
        abort_unless($post->business_id === $business->id, 404);
        $data = $request->validate(['title' => ['required', 'string', 'max:140'], 'body' => ['required', 'string', 'max:2000'], 'photo' => ['nullable', 'image', 'max:5120'], 'type' => ['required', 'in:product,offer,news'], 'price' => ['nullable', 'numeric', 'min:0'], 'discount_price' => ['nullable', 'numeric', 'min:0', 'lte:price'], 'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'], 'is_published' => ['nullable', 'boolean']]);
        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('posts', 'public');
        }
        $post->update($data + ['is_published' => $request->boolean('is_published')]);
        return back()->with('success', 'Publicación actualizada.');
    }

    public function destroy(Business $business, Post $post)
    {
        $this->ownerOnly($business);
        abort_unless($post->business_id === $business->id, 404);
        $post->delete();
        return back()->with('success', 'Publicación eliminada.');
    }

    private function ownerOnly(Business $business): void
    {
        abort_unless($business->owner_id === Auth::id() || Auth::user()?->role === 'superadmin', 403);
    }
}