<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Support\CascadeSoftDeletes;

class Farm extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $cascadeDeletes = ['lines'];

    protected $fillable = ['user_id', 'name', 'long', 'lat', 'area', 'owner', 'farm_number'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('user_id');
    }

    public function lines()
    {
        return $this->hasMany(Line::class);
    }

    // public function farm_budgets()
    // {
    //     return $this->hasMany(FarmExpenses::class);
    // }

    public function lines_budgets()
    {
        $l = request();

        $u = auth()->user()->id;

        return $this->hasMany(Line::class)->whereHas('users', function($q) use ($u, $l) {
            $q->where('user_id', '=', $u)->where('line_id', '=', $l->line_id);
        })->with('budgets');
    }

    public function overview_budgets()
    {
        $u = auth()->user()->id;

        return $this->hasMany(Line::class)
//            ->whereHas('users', function($q) use ($u) {
//            $q->where('user_id', '=', $u);
//        })
        ->with('budgets');
    }
}
