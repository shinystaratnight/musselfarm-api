<?php

namespace App\Http\Resources\Line;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class LineArchivesResource extends JsonResource
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
            'harvest_group_id' => $this->harvest_group_id,
            'length' => $this->length,
            'planned_date_harvest' => $this->planned_date_harvest,
            'planned_date' => $this->planned_date,
            'seed' => $this->seeds->name,
            'condition' => $this->condition,
            'profit_per_meter' => $this->profit_per_meter,
            'created' => Carbon::parse($this->created)->timestamp,
            'updated' => Carbon::parse($this->updated)->timestamp
        ];
    }
}
