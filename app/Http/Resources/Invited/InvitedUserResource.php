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
        $accId = $request->input('account_id');
        $acc = $this->getAccount($accId);
        $farms = $acc->getUserFarms(
            !isset($this->users->id) ? $this->id : $this->users->id
        );

        $lines = [];
        
        $userAccess = $acc->pivot->user_access == '' ? '' : json_decode($acc->pivot->user_access);
        $role = $acc->pivot->hasRole('admin');

        foreach ($farms as $farm) {
            $farm_lines = $farm->lines;
            foreach ($farm_lines as $line) {
                if ($role || $userAccess == '' || in_array($line->id, $userAccess->line_id))
                    $lines[] = $line;
            }
        }
        return [
            'user_id' => !isset($this->users->id) ? $this->id : $this->users->id,
            'email' => !isset($this->users->email) ? $this->email : $this->users->email,
            'name' => !isset($this->profile->name) ? !isset($this->users->profile->name) ? null : $this->users->profile->name : $this->profile->name,
            'status' => !isset($this->status) ? "active" : $this->status,
            'role' => $this->getAccount($request->input('account_id'))->pivot->roles[0]['name'],
            'farms' => $farms ? array_map(function($farm) {
                return $farm['id'];
            }, $farms->toArray()) : [],
            'lines' => $lines ? array_map(function($line) {
                return $line['id'];
            }, $lines) : [],
        ];
    }
}
