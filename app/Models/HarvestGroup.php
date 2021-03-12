<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HarvestGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = ['line_id',
                           'name',
                           'planned_date_harvest',
                           'harvest_complete_date',
                           'planned_date',
                           'planned_date_original',
                           'planned_date_harvest_original',
                           'color',
                           'seed_id',
                           'condition',
                           'profit_per_meter',
                           'density',
                           'drop',
                           'floats',
                           'spacing',
                           'submersion'];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function seasons()
    {
        return $this->hasOne(Season::class, 'id', 'name');
    }
    public function lines()
    {
        return $this->belongsTo(Line::class, 'line_id', 'id');
    }

    public function seeds()
    {
        return $this->belongsTo(FarmUtil::class, 'seed_id', 'id');
    }

    public function archives()
    {
        return $this->hasOne(LineArchive::class);
    }
}
