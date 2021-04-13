<?php

namespace App\Http\Resources\Budget;

use Illuminate\Http\Resources\Json\JsonResource;

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
        $year_budget = [];
        foreach($this->farm_budgets as $budget) {
            if (
                $budget->expense_date &&
                (strtotime($year . '-01-01') . '000' )<= $budget->expense_date &&
                (strtotime($year . '-12-31') . '000' )> $budget->expense_date
            ) {
                $budget->expense_date = $budget->expense_date ? $budget->expense_date : strtotime($budget->created_at->format('Y-m-d')) . '000';
                array_push($year_budget, $budget);
            } else if(
                !$budget->expense_date &&
                strtotime($year . '-01-01') <= strtotime($budget->created_at) &&
                strtotime($year . '-12-31') > strtotime($budget->created_at)
            ) {
                $budget->expense_date = $budget->expense_date ? $budget->expense_date : strtotime($budget->created_at->format('Y-m-d')) . '000';
                array_push($year_budget, $budget);
            }
        }
        return [
            'farm_id' => $this->id,
            'farm_name' => $this->name,
            'farm_budget' => $year_budget,
            'lines' => LineBudgetResource::collection($this->lines)
        ];
    }
}
