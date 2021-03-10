<?php

namespace App\Http\Resources\Farm;

use App\Http\Resources\Line\LineLastAssessmentResource;
use App\Traits\IdleTrait;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Line\LineResource;

class FarmResource extends JsonResource
{
    use IdleTrait;
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
            'user_id' => $this->user_id,
            'name' => $this->name,
            'farm_number' => $this->farm_number,
            'location' => [
                'lat' => $this->lat,
                'lng' => $this->long,
            ],
            'idle_avg' => 0,
//            'idle_avg' => $this->idleAvgForFarm($this->id),
            'area' => $this->area,
            'owners' => json_decode($this->owner, true),
            'lines' => LineLastAssessmentResource::collection($this->lines)
        ];
    }
}
