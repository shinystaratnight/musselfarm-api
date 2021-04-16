<?php

namespace App\Http\Resources\Budget;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Budget\BudgetLinesResourse;

use App\Repositories\Line\LineBudgetRepository as Budget;

class BudgetFarmsLinesResourse extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {

        $year = -1;
        $lines = LineBudgetResource::collection($this->lines);
        $farm_expense = Budget::farmExpenseInfo($year, $this->farm_budgets, $this->lines);

        return [
            'farm_id' => $this->id,
            'farm_name' => $this->name,
            'farm_expense_info' => $farm_expense['info'],
            'lines' => BudgetLinesResourse::collection($this->lines)
        ];
    }
}
