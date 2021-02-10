<?php

namespace App\Http\Resources\Invited;

use Illuminate\Http\Resources\Json\JsonResource;

class FarmAccessResource extends JsonResource
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
            'farm_id' => $this->whenPivotLoaded('farm_user', function () {
                return $this->pivot->farm_id;
            })
        ];
    }
}
