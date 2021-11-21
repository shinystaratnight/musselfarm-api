<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "line_assessment";

    protected $fillable = ['harvest_group_id',
                           'color',
                           'condition_min',
                           'condition_max',
                           'condition_avg',
                           'blues',
                           'tones',
                           'date_assessment',
                           'planned_date_harvest',
                           'condition_score',
                           'comment'];

    public function group()
    {
        return $this->belongsTo(HarvestGroup::class,'harvest_group_id', 'id');
    }

    public function lines()
    {
        return $this->belongsTo(Line::class);
    }

    public function harvests()
    {
        return $this->belongsTo(HarvestGroup::class);
    }

    public function photos()
    {
        return $this->hasMany(AssessmentPhoto::class);
    }
}
