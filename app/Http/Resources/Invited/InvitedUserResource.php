<?php

namespace App\Http\Resources\Invited;

use Illuminate\Http\Resources\Json\JsonResource;

class InvitedUserResource extends JsonResource
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
            'user_id' => !isset($this->users->id) ? $this->id : $this->users->id,
            'email' => !isset($this->users->email) ? $this->email : $this->users->email,
            'name' => !isset($this->profile->name) ? !isset($this->users->profile->name) ? null : $this->users->profile->name : $this->profile->name,
            'status' => !isset($this->status) ? "active" : $this->status,
            'role' => !isset($this->roles[0]['name']) ? !isset($this->users->roles[0]['name']) ? null : $this->users->roles[0]['name'] : $this->roles[0]['name']
        ];
    }
}
