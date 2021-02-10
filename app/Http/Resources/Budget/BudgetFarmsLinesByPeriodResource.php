<?php

namespace App\Http\Resources\Budget;

use Illuminate\Http\Resources\Json\JsonResource;

class BudgetFarmsLinesByPeriodResource extends JsonResource
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
            'farm_id' => $this->id,
            'farm_name' => $this->name,
            'lines' => BudgetLineByPeriodResource::collection($this->lines_budgets)
        ];
    }
}
