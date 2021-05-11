<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['farm_id',
                           'line_id',
                           'due_date',
                           'creator_id',
                           'charger_id',
                           'active'];

}
