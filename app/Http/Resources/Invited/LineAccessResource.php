<?php

namespace App\Http\Resources\Invited;

use Illuminate\Http\Resources\Json\JsonResource;

class LineAccessResource extends JsonResource
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
            'line_id' => $this->whenPivotLoaded('line_user', function () {
                return $this->pivot->line_id;
            })
        ];
    }
}
