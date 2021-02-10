<?php

namespace App\Http\Resources\Group;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class GroupForLineFarmResource extends JsonResource
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
            'harvest_complete_date' => $this->harvest_complete_date !== '0' ? $this->harvest_complete_date : null,
            'planned_date_harvest' => $this->planned_date_harvest,
            'planned_date' => $this->planned_date,
            'planned_date_original' =>  $this->planned_date_original,
            'color' =>  $this->color,
            'seed' =>  $this->seeds->name,
            'condition' =>  $this->condition,
            'profit_per_meter' =>  $this->profit_per_meter
        ];
    }
}
