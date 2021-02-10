<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UpdateUserProfileResource extends JsonResource
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
            'id' => $request['id'],
            'user_id' => $request['user_id'],
            'name' => $request['name'],
            'phone_number' => $request['phone_number'],
            'company_name' => $request['company_name'],
            'company_address' => $request['company_address'],
            'avatar' => $request['avatar']
        ];
    }
}
