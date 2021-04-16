<?php

namespace App\Http\Resources\Budget;

use Illuminate\Http\Resources\Json\JsonResource;

use App\Repositories\Line\LineBudgetRepository as Budget;

class FarmBudgetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $year = $request->input('year');
        $lines = LineBudgetResource::collection($this->lines);

        $farm_expense = Budget::farmExpenseInfo($year, $this->farm_budgets, $this->lines);
        return [
            'farm_id' => $this->id,
            'farm_name' => $this->name,
            'farm_expense_info' => $farm_expense['info'],
            'farm_expenses' => $farm_expense['expenses'],
            'lines' => $lines,
        ];
    }
}
