<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\User\GetPermissionsResource;

class UserResource extends JsonResource
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
            'full_name' => $this->name,
            'email' => $this->email,
            'role' => $this->roles[0]['name'],
            'permissions' => GetPermissionsResource::collection($this->user->permissions),
            'company_name' => $this->profile->company_name,
            'company_address' => $this->profile->company_address,
            'phone_number' => $this->profile->phone_number
        ];
    }
}
