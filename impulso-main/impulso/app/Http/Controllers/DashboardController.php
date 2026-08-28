<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(Business $business)
    {
        abort_unless($business->owner_id === Auth::id() || $business->members()->whereKey(Auth::id())->exists(), 403);
        $from = now()->subDays(30)->startOfDay();
        $expenses = $business->expenses()->where('expense_date', '>=', $from)->with('category')->get();
        $categoryTotals = $expenses->groupBy(fn ($expense) => $expense->category->name)
            ->map(fn ($items) => $items->sum('amount'))->sortDesc();
        return view('dashboard', [
            'business' => $business,
            'totalExpenses' => $expenses->sum('amount'),
            'totalIncomes' => $business->incomes()->where('income_date', '>=', $from)->sum('amount'),
            'categoryTotals' => $categoryTotals,
            'incomeTotals' => $business->incomes()->where('income_date', '>=', $from)->get()->groupBy(fn ($income) => $income->source)->map(fn ($items) => $items->sum('amount'))->sortDesc(),
            'appointments' => $business->appointments()->whereIn('status', ['pending', 'confirmed'])->count(),
            'inquiries' => $business->inquiries()->where('status', 'pending')->count(),
            'recentExpenses' => $business->expenses()->with('category')->latest('expense_date')->limit(5)->get(),
            'recentIncomes' => $business->incomes()->latest('income_date')->limit(5)->get(),
        ]);
    }
}