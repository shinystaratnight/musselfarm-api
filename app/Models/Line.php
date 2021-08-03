<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Line extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['line_name', 'farm_id', 'length'];

    public function farms()
    {
        return $this->belongsTo(Farm::class,  'farm_id', 'id');
    }

    // public function users()
    // {
    //     return $this->belongsToMany(User::class)->withPivot('user_id');
    // }

    public function seeds()
    {
        return $this->belongsTo(Seed::class, 'seed_id', 'id');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'line_id', 'id');
    }

    public function harvests()
    {
        return $this->hasMany(HarvestGroup::class, 'line_id', 'id');
    }


    public function budgets()
    {
        return $this->hasMany(LineBudget::class)->with('expenses');
    }

    public function overview_budgets()
    {
        return $this->hasMany(LineBudget::class)->with('expenses');
    }
}
