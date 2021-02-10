<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChartData extends Model
{
    use HasFactory;

    protected $fillable = ['user_id',
                           'farm_name',
                           'line_name',
                           'chart_date',
                           'info'];
}
