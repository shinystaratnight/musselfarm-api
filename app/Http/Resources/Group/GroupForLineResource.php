<?php

namespace App\Http\Resources\Group;

use App\Http\Resources\Line\LineArchivesResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Assessment\AssessmentForGroupResource;

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
        return [
            'id' => $this->id,
            'name' => $this->name,
            'season_name' => $this->seasons->season_name,
            'harvest_complete_date' => $this->harvest_complete_date !== '0' ? $this->harvest_complete_date : null,
            'planned_date_harvest' => $this->planned_date_harvest,
            'planned_date_harvest_original' => $this->planned_date_harvest_original ,
            'planned_date' => $this->planned_date,
            'planned_date_original' =>  $this->planned_date_original,
            'color' =>  $this->color,
            'seed' =>  $this->seeds->name,
            'condition' =>  $this->condition,
            'density' => $this->density,
            'drop' => $this->drop,
            'floats' => $this->floats,
            'spacing' => $this->spacing,
            'submersion' => $this->submersion,
            'profit_per_meter' =>  $this->profit_per_meter,
            'archive_line' => new LineArchivesResource($this->archives),
            'assessments' => AssessmentForGroupResource::collection($this->assessments)
        ];
    }
}

