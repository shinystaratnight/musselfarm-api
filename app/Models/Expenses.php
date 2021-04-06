<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expenses extends Model
{
    use HasFactory;

    protected $fillable = ['line_budget_id', 'type', 'expenses_name', 'price_budget', 'price_actual', 'rdata', 'expense_date'];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function budgets()
    {
        return $this->belongsTo(LineBudget::class);
    }

    public function logs()
    {
        return $this->belongsTo(BudgetLog::class);
    }
}
