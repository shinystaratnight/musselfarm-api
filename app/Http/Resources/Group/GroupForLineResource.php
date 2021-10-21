<?php

namespace App\Http\Resources\Group;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Assessment\AssessmentForGroupResource;
use App\Http\Resources\Line\LineArchivesResource;
use App\Http\Resources\Line\LineArchivesResourceCatchSpat;

class GroupForLineResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if ($this->catch_spat == '0') {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'season_name' => $this->seasons->season_name,
                'harvest_complete_date' => $this->harvest_complete_date !== '0' ? $this->harvest_complete_date : null,
                'planned_date_harvest' => $this->planned_date_harvest,
                'planned_date_harvest_original' => $this->planned_date_harvest_original,
                'planned_date' => $this->planned_date,
                'planned_date_original' =>  $this->planned_date_original,
                'color' =>  $this->color,
                'seed' =>  $this->seeds->name,
                'condition' =>  $this->condition,
                'density' => $this->density,
                'drop' => $this->drop,
                'spat_size' => $this->spat_size,
                'signature' => $this->signature,
                'line_length' => $this->line_length,
                'floats' => $this->floats,
                'spacing' => $this->spacing,
                'submersion' => $this->submersion,
                'profit_per_meter' =>  $this->profit_per_meter,
                'archive_line' => new LineArchivesResource($this->archives),
                'catch_spat' => $this->catch_spat,
                'assessments' => AssessmentForGroupResource::collection($this->assessments)
            ];
        } else {
            return [
                'id' => $this->id,
                'name' => $this->name,
                'season_name' => $this->seasons->season_name,
                'harvest_complete_date' => $this->harvest_complete_date !== '0' ? $this->harvest_complete_date : null,
                'planned_date_harvest' => $this->planned_date_harvest,
                'planned_date_harvest_original' => $this->planned_date_harvest_original,
                'planned_date' => $this->planned_date,
                'planned_date_original' =>  $this->planned_date_original,
                'color' =>  '',
                'seed' =>  '',
                'condition' =>  '',
                'density' => '',
                'drop' => '',
                'spat_size' => '',
                'signature' => $this->signature,
                'line_length' => $this->line_length,
                'floats' => '',
                'spacing' => '',
                'submersion' => '',
                'profit_per_meter' =>  '',
                'archive_line' => new LineArchivesResourceCatchSpat($this->archives),
                'catch_spat' => $this->catch_spat,
                'assessments' => []
            ];
        }
    }
}
