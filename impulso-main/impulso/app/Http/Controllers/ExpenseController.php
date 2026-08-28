<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function store(Request $request, Business $business)
    {
        abort_unless($business->owner_id === Auth::id(), 403);
        $data = $request->validate(['expense_category_id' => ['required', 'exists:expense_categories,id'], 'description' => ['required', 'string', 'max:160'], 'amount' => ['required', 'numeric', 'min:0'], 'expense_date' => ['required', 'date']]);
        $business->expenses()->create($data + ['created_by' => Auth::id()]);
        return back()->with('success', 'Gasto registrado.');
    }

    public function index(Business $business)
    {
        abort_unless($business->owner_id === Auth::id(), 403);
        return view('expenses.index', ['business' => $business, 'expenses' => $business->expenses()->with('category')->latest('expense_date')->paginate(12), 'categories' => ExpenseCategory::orderBy('name')->get()]);
    }
}