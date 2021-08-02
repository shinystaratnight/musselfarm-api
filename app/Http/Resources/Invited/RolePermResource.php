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
        $uac = $this->getAccount($request->input('account_id'));
        $ua_access = json_decode($uac->pivot->user_access);
        $farms = $uac->farms;
        $lines = [];
        foreach ($farms as $farm) {
            foreach ($farm->lines as $line) {
                if (in_array($line->id, $ua_access->line_id)) {
                    $lines[] = [
                        'line_id' => $line->id
                    ];
                }
            }
        }
        return [
            'user_id' => $this->id,
            'email' => $this->email,
            'role' => RoleResource::collection($uac->pivot->roles),
            'permissions' => PermissionResource::collection($uac->pivot->permissions),
            'farms' => FarmAccessResource::collection($uac->farms),
            'lines' => $lines
        ];
    }
}

