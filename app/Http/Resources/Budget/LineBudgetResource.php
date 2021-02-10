<?php

namespace App\Http\Resources\Budget;

use Illuminate\Http\Resources\Json\JsonResource;

class LineBudgetResource extends JsonResource
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
            'line_id' => $this->id,
            'line_name' => $this->line_name,
            'length' => $this->length,
            'line_budget' => BudgetBudgetResource::collection($this->budgets)
        ];
    }
}
