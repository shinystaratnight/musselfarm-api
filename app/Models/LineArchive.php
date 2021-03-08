<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineArchive extends Model
{
    use HasFactory;

    protected $fillable = ['harvest_group_id',
                           'length',
                           'planned_date_harvest',
                           'planned_date_harvest_original',
                           'planned_date',
                           'seed_id',
                           'condition',
                           'profit_per_meter'];

    public function harvests()
    {
        return $this->belongsTo(HarvestGroup::class);
    }

    public function seeds()
    {
        return $this->belongsTo(FarmUtil::class, 'seed_id', 'id');
    }
}
