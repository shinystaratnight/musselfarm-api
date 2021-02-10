<?php

namespace App\Http\Resources\Budget;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetLogsResource extends JsonResource
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
            'farm_name' => $this->farms->name,
            'line_name' => $this->lines->line_name,
            'type' => $this->row_name,
            'type_human' => $this->human_name,
            'change' => ["old" => $this->old, "new" => $this->new],
            'user_name' => $this->users->profile->name,
            'date' => Carbon::parse($this->created_at)->timestamp,
            'comment' => $this->comment,
            'id' => $this->id,
            'key' => $this->id
        ];
    }
}
