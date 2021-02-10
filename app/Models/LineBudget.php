<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineBudget extends Model
{
    use HasFactory;

    protected $fillable = ['line_id',
                           'planned_harvest_tones',
                           'budgeted_harvest_income',
                           'start_budget',
                           'end_budget',
                           'length_actual',
                           'length_budget',
                           'planned_harvest_tones_actual',
                           'budgeted_harvest_income_actual'];

    public function expenses()
    {
        return $this->hasMany(Expenses::class);
    }

    public function logs()
    {
        return $this->belongsTo(BudgetLog::class);
    }
}
