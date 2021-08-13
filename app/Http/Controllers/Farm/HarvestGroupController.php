<?php

namespace App\Http\Controllers\Farm;

use App\Http\Requests\Harvest\UpdateHarvestGroupRequest;
use App\Models\ChartData;
use App\Models\Farm;
use App\Models\HarvestGroup;
use App\Models\Line;
use App\Models\Task;
use App\Models\User;
use App\Models\Account;
use App\Models\Automation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Harvest\HarvestCompleteRequest;
use App\Http\Requests\Harvest\CreateHarvestGroupRequest;
use App\Models\LineArchive;
use App\Models\LineBudget;
use App\Repositories\Harvest\HarvestRepositoryInterface as Harvest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class HarvestGroupController extends Controller
{
    private $harvestRepo;

    public function __construct(Harvest $harvest)
    {
        $this->harvestRepo = $harvest;
    }

    public function index()
    {

    }

    public function store(CreateHarvestGroupRequest $request)
    {
        $attr = $request->validated();

        return $this->harvestRepo->startHarvest($attr);
    }

    public function show(HarvestGroup $harvestGroup)
    {
        //
    }

    public function update(UpdateHarvestGroupRequest $request, HarvestGroup $harvest)
    {
        $attr = $request->validated();

        $harvest->update($attr);

        $harvest->planned_date_harvest_original = $attr['planned_date_harvest'];

        $harvest->save();

        $currentLine = Line::find($harvest->line_id);
        $currentLine->length = $attr['line_length'];
        $currentLine->update();

        return response()->json(['status' => 'Success'], 200);
    }

    public function destroy(HarvestGroup $harvestGroup)
    {
        //
    }

    public function harvestComplete(HarvestCompleteRequest $request)
    {
        $attr = $request->validated();

        $harvest = HarvestGroup::where('id', $attr['harvest_group_id'])->first();
        $harvest->planned_date_harvest = $attr['harvest_complete_date'];

        $currentLine = Line::find($harvest->line_id);

        $requestHarvestDate = Carbon::createFromTimestamp($harvest->planned_date_harvest)->year;

        $currentYear = Carbon::now()->year;

        // automation task start
        $automations = Automation::where([
            'condition' => 'Harvesting',
            'action' => 'Completed',
            'account_id' => $attr['account_id']
        ])->get();

        foreach($automations as $automation) {   
            
            $due_date = Carbon::createFromTimestamp($attr['harvest_complete_date'])->add($automation->time, $automation->unit)->timestamp * 1000;

            $access = Account::find($attr['account_id'])->getAccUserHasPermission($automation->creator_id, 'line', $harvest->line_id);
            if ($automation->assigned_to && $access) {
                $access = Account::find($attr['account_id'])->getAccUserHasPermission($automation->assigned_to, 'line', $harvest->line_id);
            }
            if ($access) {
                $task = Task::create([
                    'account_id' => $attr['account_id'],
                    'creator_id' => $automation->creator_id,
                    'farm_id' => $currentLine->farm_id,
                    'title' => $automation->title,
                    'content' => $automation->description,
                    'assigned_to' => $automation->assigned_to ? $automation->assigned_to : 0,
                    'line_id' => $harvest->line_id,
                    'due_date' => $due_date,
                ]);
            }
        }
        // automation task end

        if($currentYear == $requestHarvestDate) {

            $completedHarvest = HarvestGroup::where(['id' => $attr['harvest_group_id'], 'harvest_complete_date' => 0])
                ->update([
                    'harvest_complete_date' => $harvest->planned_date_harvest,
                    'planned_date_harvest' => $harvest->planned_date_harvest,
                ]);

            if ($completedHarvest) {

                $archiveData = HarvestGroup::where('id', $attr['harvest_group_id'])->with('lines')->first();

                $budget = LineBudget::where('line_id', $archiveData->line_id)->first();

                $budget->planned_harvest_tones_actual += $attr['planned_harvest_tones_actual'];

                $budget->budgeted_harvest_income_actual += $attr['budgeted_harvest_income_actual'];

                $budget->save();

                $profitPerMeterCalculation = $attr['budgeted_harvest_income_actual'] / $budget->length_actual;

                $archiveData->profit_per_meter = round($profitPerMeterCalculation, 2);

                $archiveData->save();

                LineArchive::create([
                    'harvest_group_id' => $attr['harvest_group_id'],
                    'length' => $archiveData->line_length,
                    'planned_date_harvest' => $archiveData->planned_date_harvest,
                    'planned_date_harvest_original' => $archiveData->planned_date_harvest,
                    'planned_date' => $archiveData->planned_date,
                    'seed_id' => $archiveData->seed_id,
                    'condition' => $archiveData->condition,
                    'profit_per_meter' => $archiveData->profit_per_meter
                ]);
            }
            return response()->json(['status' => 'Success'], 200);
        } else  {

            $startOfYear = Carbon::parse('first day of January ' . $requestHarvestDate)->timestamp;

            $endOfYear = Carbon::parse('last day of December ' . $requestHarvestDate)->timestamp;

            $budget = LineBudget::where(['line_id' => $harvest->line_id,
                                         'start_budget' => $startOfYear,
                                         'end_budget' => $endOfYear])->first();

            if(!$budget) {
                $budget = LineBudget::create([
                    'line_id' => $harvest->line_id,
                    'start_budget' => $startOfYear,
                    'end_budget' => $endOfYear,
                    'length_actual' => $currentLine->length,
                    'length_budget' => $currentLine->length,
                ]);
            }

            $completedHarvest = HarvestGroup::where(['id' => $attr['harvest_group_id'], 'harvest_complete_date' => 0])
                ->update([
                    'harvest_complete_date' => $harvest->planned_date_harvest,
                    'planned_date_harvest' => $harvest->planned_date_harvest
                ]);

            if ($completedHarvest) {

                $archiveData = HarvestGroup::where('id', $attr['harvest_group_id'])->with('lines')->first();

                $budget->planned_harvest_tones_actual += $attr['planned_harvest_tones_actual'];

                $budget->budgeted_harvest_income_actual += $attr['budgeted_harvest_income_actual'];

                $budget->save();

                $profitPerMeterCalculation = $attr['budgeted_harvest_income_actual'] / $budget->length_actual;

                $archiveData->profit_per_meter = round($profitPerMeterCalculation, 2);

                $archiveData->save();

                LineArchive::create([
                    'harvest_group_id' => $attr['harvest_group_id'],
                    'length' => $archiveData->line_length,
                    'planned_date_harvest' => $archiveData->planned_date_harvest,
                    'planned_date_harvest_original' => $archiveData->planned_date_harvest,
                    'planned_date' => $archiveData->planned_date,
                    'seed_id' => $archiveData->seed_id,
                    'condition' => $archiveData->condition,
                    'profit_per_meter' => $archiveData->profit_per_meter
                ]);
            }
            return response()->json(['status' => 'Success'], 200);

        }
    }
}


