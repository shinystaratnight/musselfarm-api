<?php

namespace App\Http\Resources\Seed;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SeedResource extends JsonResource
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
            'created' =>  Carbon::parse($this->created_at)->format('Y-m-d')
        ];
    }
}
