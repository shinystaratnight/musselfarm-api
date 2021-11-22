<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LineSeeding extends Model
{
    use HasFactory, SoftDeletes;

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'line_id',
        'season_id',
        'seed_id',
        'planned_date',
        'planned_date_harvest',
        'seed_length',
        'density',
        'drop',
        'floats',
        'spacing',
        'submersion',
        'spat_size',
        'condition'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function seasons()
    {
        return $this->hasOne(Season::class, 'id', 'season_id');
    }

    public function lines()
    {
        return $this->belongsTo(Line::class, 'line_id', 'id');
    }

    public function seeds()
    {
        return $this->belongsTo(FarmUtil::class, 'seed_id', 'id');
    }
}
