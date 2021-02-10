<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceCostActual extends Model
{
    use HasFactory;

    protected $fillable = ['line_budget_id', 'maintenance_name_actual', 'price_actual'];

    public function budgets()
    {
        return $this->belongsTo(LineBudget::class);
    }
}
