<?php

namespace App\Http\Resources\Overview;

use Illuminate\Http\Resources\Json\JsonResource;

class NextSeedingResource extends JsonResource
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
            'farm_id' => $this->lines->farms->id,
            'line_id' => $this->line_id,
            'date' => $this->planned_date
        ];
    }
}
