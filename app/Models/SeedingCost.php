<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeedingCost extends Model
{
    use HasFactory;

    protected $fillable = ['line_budget_id', 'spat_name', 'price'];

    public function budgets()
    {
        return $this->belongsTo(LineBudget::class, 'line_budget_id', 'id');
    }
}
