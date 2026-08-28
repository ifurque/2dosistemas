<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['business_id', 'expense_category_id', 'created_by', 'description', 'amount', 'expense_date'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'expense_date' => 'date']; }
    public function category() { return $this->belongsTo(ExpenseCategory::class, 'expense_category_id'); }
}