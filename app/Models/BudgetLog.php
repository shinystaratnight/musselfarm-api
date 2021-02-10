<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetLog extends Model
{
    use HasFactory;

    protected $fillable = ['user_id',
                           'farm_id',
                           'line_id',
                           'line_budget_id',
                           'expenses_id',
                           'row_name',
                           'human_name',
                           'old',
                           'new',
                           'comment'];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function farms()
    {
        return $this->belongsTo(Farm::class, 'farm_id', 'id');
    }

    public function lines()
    {
        return $this->belongsTo(Line::class, 'line_id', 'id');
    }

    public function budgets()
    {
        return $this->belongsTo(LineBudget::class, 'line_budget_id', 'id');
    }

    public function expenses()
    {
        return $this->belongsTo(Expenses::class, 'expenses_id', 'id');
    }
}
