<?php

namespace App\Http\Resources\Budget;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Budget\BudgetResourse;

class BudgetLinesResourse extends JsonResource
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
            'line_budget' => BudgetResourse::collection($this->overview_budgets)
        ];
    }
}
