<?php

namespace App\Http\Resources\Budget;

use Illuminate\Http\Resources\Json\JsonResource;

class ExpensesResource extends JsonResource
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
            'line_budget_id' => $this->line_budget_id,
            'type' => $this->type,
            'expenses_name' => $this->expenses_name,
            'price_budget' => $this->price_budget,
            'price_actual' => $this->price_actual,

        ];
    }
}
