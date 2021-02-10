<?php

namespace App\Http\Resources\Invited;

use App\Http\Resources\Invited\PermissionResource;
use App\Http\Resources\Invited\RoleResource;
use App\Http\Resources\Invited\FarmAccessResource;
use App\Http\Resources\Invited\LineAccessResource;
use Illuminate\Http\Resources\Json\JsonResource;

class RolePermResource extends JsonResource
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
            'user_id' => $this->id,
            'email' => $this->email,
            'role' => RoleResource::collection($this->roles),
            'permissions' => PermissionResource::collection($this->permissions),
            'farms' => FarmAccessResource::collection($this->whenLoaded('farms')),
            'lines' => LineAccessResource::collection($this->whenLoaded('lines'))
        ];
    }
}

