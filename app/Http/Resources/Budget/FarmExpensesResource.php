<?php

namespace App\Http\Resources\Budget;

use Illuminate\Http\Resources\Json\JsonResource;

class FarmExpensesResource extends JsonResource
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
            'farm_id' => $this->farm_id,
            'date' => $this->date,
            'type' => $this->type,
            'expenses_name' => $this->expenses_name,
            'price_budget' => $this->price_budget,
            'price_actual' => $this->price_actual,
            'expense_date' => $this->expense_date ? $this->expense_date : strtotime($this->created_at->format('Y-m-d')) . '000',
        ];
    }
}
