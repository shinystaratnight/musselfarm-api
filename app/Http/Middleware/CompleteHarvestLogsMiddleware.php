<?php

namespace App\Http\Middleware;

use App\Models\BudgetLog;
use App\Models\HarvestGroup;
use App\Models\LineBudget;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class CompleteHarvestLogsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $oldHarvestGroup = HarvestGroup::where('id', request()->input('harvest_group_id'))->first();

        if (Carbon::now()->year == Carbon::createFromTimestamp($oldHarvestGroup->planned_date_harvest)->year) {
            $olb = LineBudget::where('end_budget', 0)
                ->where('line_id', $oldHarvestGroup->line_id)->first();
        } else {
            $olb = LineBudget::where('start_budget', '<=', $oldHarvestGroup->planned_date)
                ->where('end_budget', '>=', $oldHarvestGroup->planned_date_harvest)
                ->where('line_id', $oldHarvestGroup->line_id)->first();
        }

        $response = $next($request);

            $harvestGroup = HarvestGroup::where('id', request()->input('harvest_group_id'))->first();

            if (Carbon::now()->year == Carbon::createFromTimestamp($harvestGroup->planned_date_harvest)->year) {
                $lb = LineBudget::where('end_budget', 0)
                    ->where('line_id', $harvestGroup->line_id)->first();
            } else {
                $lb = LineBudget::where('start_budget', '<=', $harvestGroup->planned_date_harvest)
                    ->where('end_budget', '>=', $harvestGroup->planned_date_harvest)
                    ->where('line_id', $harvestGroup->line_id)->first();
            }

            $oldTones = 0;
            $oldIncome = 0;

            if(isset($olb->planned_harvest_tones_actual)) {
                $oldTones = $olb->getOriginal('planned_harvest_tones_actual');
            }

            if(isset($olb->budgeted_harvest_income_actual)) {
                $oldIncome = $olb->getOriginal('budgeted_harvest_income_actual');
            }

            BudgetLog::create([
                'user_id' => auth()->user()->id,
                'farm_id' => $harvestGroup->lines->farm_id,
                'line_id' => $harvestGroup->line_id,
                'line_budget_id' => $lb->id,
                'row_name' => 'planned_harvest_tones_actual',
                'human_name' => 'Harvest tonnes (Actual)',
                'old' => $oldTones,
                'new' => $oldTones + $request->input('planned_harvest_tones_actual'),
                'comment' => 'Complete harvest - Harvest tonnes (Actual).
                              Harvest name: "' . $harvestGroup->name . '".
                              Harvest seeded: ' . Carbon::createFromTimestamp($harvestGroup->planned_date)->toDateTimeString() . '.
                              Harvest completed: ' . Carbon::createFromTimestamp($harvestGroup->planned_date_harvest)->toDateTimeString() . '.',
            ]);

            BudgetLog::create([
                'user_id' => auth()->user()->id,
                'farm_id' => $harvestGroup->lines->farm_id,
                'line_id' => $harvestGroup->line_id,
                'line_budget_id' => $lb->id,
                'row_name' => 'budgeted_harvest_income_actual',
                'human_name' => 'Harvest income (Actual)',
                'old' => $oldIncome,
                'new' => $oldIncome + $request->input('budgeted_harvest_income_actual'),
                'comment' => 'Complete harvest - Harvest income (Actual).
                              Harvest name: "' . $harvestGroup->name . '".
                              Harvest seeded: ' . Carbon::createFromTimestamp($harvestGroup->planned_date)->toDateTimeString() . '.
                              Harvest completed: ' . Carbon::createFromTimestamp($harvestGroup->planned_date_harvest)->toDateTimeString() . '.',
            ]);

        return $response;

    }
}
