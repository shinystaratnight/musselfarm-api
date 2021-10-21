<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HarvestGroup extends Model
{
    use HasFactory, SoftDeletes;

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'line_id',
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
        'spat_size',
        'floats',
        'spacing',
        'submersion',
        'line_length',
        'company',
        'vessel',
        'harvest_number',
        'number_of_bags',
        'tag_color',
        'port_of_unload',
        'crop_owner',
        'growing_area',
        'delivered_to',
        'packhouse',
        'start_time',
        'finish_time',
        'bags_clean',
        'area_open_for_harvest',
        'trucks_booked',
        'more_clean_bags_on_truck',
        'shell_length',
        'shell_condition',
        'mussels',
        'meat_yield',
        'blues',
        'marine_waste',
        'backbone_ok',
        'backbone_replace',
        'lights_ids_in_place',
        'flotation_on_farm',
        'number_of_rope_bags',
        'product_left_on_line',
        'harvestor_name',
        'signature',
        'comments',
        'catch_spat'
    ];

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
