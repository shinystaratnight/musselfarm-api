<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceCost extends Model
{
    use HasFactory;

    protected $fillable = ['line_budget_id', 'maintenance_name', 'price'];

    public function budgets()
    {
        return $this->belongsTo(LineBudget::class);
    }
}
