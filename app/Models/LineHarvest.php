<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LineHarvest extends Model
{
    use HasFactory, SoftDeletes;

    protected $dateFormat = 'Y-m-d H:i:s';

    protected $fillable = [
        'seeding_id',
        'harvest_complete_date',
        'tonnes_harvested',
        'harvest_income',
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
        'comments'
    ];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    public function line()
    {
        return $this->belongsTo(LineSeeding::class, 'seeding_id', 'id');
    }
}
