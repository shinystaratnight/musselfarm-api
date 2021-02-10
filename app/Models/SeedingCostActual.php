<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeedingCostActual extends Model
{
    use HasFactory;

    protected $fillable = ['line_budget_id', 'spat_name_actual', 'price_actual'];

    public function budgets()
    {
        return $this->belongsTo(LineBudget::class);
    }
}
