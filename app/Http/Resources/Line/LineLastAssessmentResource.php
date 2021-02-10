<?php

namespace App\Http\Resources\Line;

use App\Http\Resources\Group\GroupForLineResource;
use App\Models\HarvestGroup;
use App\Traits\IdleTrait;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class LineLastAssessmentResource extends JsonResource
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
            'line_idle' => $this->idleCurrentForLine($this->id),
            'id' => $this->id,
            'line_name' => $this->line_name,
            'farm_id' => $this->farms->id,
            'farm_name' => $this->farms->name,
            'length' => $this->length,
            'group' => new GroupForLineResource($this->harvests->last())
        ];
    }
}
