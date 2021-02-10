<?php

namespace App\Http\Resources\Budget;

use App\Http\Resources\Budget\MaintenanceResourse;
use App\Http\Resources\Budget\SeedingResourse;
use App\Http\Resources\Budget\MaintenanceActualResource;
use App\Http\Resources\Budget\SeedingActualResource;
use Illuminate\Http\Resources\Json\JsonResource;


class BudgetResourse extends JsonResource
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
            'budget_id' => $this->id,
            'length_budget' => $this->length_budget,
            'length_actual' => $this->length_actual,
            'start_budget' => $this->start_budget,
            'end_budget' => $this->end_budget,
            'planned_harvest_tones' => $this->planned_harvest_tones,
            'planned_harvest_tones_actual' => $this->planned_harvest_tones_actual,
            'budgeted_harvest_income' => $this->budgeted_harvest_income,
            'budgeted_harvest_income_actual' => $this->budgeted_harvest_income_actual,
            'expenses' => ExpensesResource::collection($this->expenses)
        ];
    }
}
