<?php

namespace App\Repositories\Harvest;

use App\Models\ChartData;
use App\Models\HarvestGroup;
use App\Models\Line;
use App\Models\User;
use App\Models\Task;
use App\Models\Account;
use App\Models\Automation;
use App\Models\LineBudget;
use App\Traits\CheckDatesHarvestsTrait;
use Carbon\Carbon;

class HarvestRepository implements HarvestRepositoryInterface
{
    use CheckDatesHarvestsTrait;

    public function startHarvest($attr, $return=false)
    {
        if($this->checkStartSeeding($attr['line_id'], $attr['planned_date'], $attr['planned_date_harvest'])) {

            HarvestGroup::create([
                'line_id' => $attr['line_id'],
                'name' => $attr['name'],
                'planned_date_harvest' => $attr['planned_date_harvest'],
                'planned_date_harvest_original' => $attr['planned_date_harvest'],
                'planned_date' => $attr['planned_date'],
                'planned_date_original' => $attr['planned_date'],
                'seed_id' => $attr['seed_id'],
                'density' => $attr['density'],
                'drop' => $attr['drop'],
                'spat_size' => $attr['spat_size'],
                'line_length' => $attr['line_length'],
                'floats' => $attr['floats'],
                'spacing' => $attr['spacing'],
                'submersion' => $attr['submersion'],
            ]);

            // automation task start
            $automations = Automation::where('account_id', $attr['account_id'])->get();

            $currentLine = Line::find($attr['line_id']);
            foreach($automations as $automation) {
                $type = $automation->action;
                $due_date = 0;
                if ($automation->condition == 'Seeding') {
                    if ($type == 'Created') {
                        $due_date = Carbon::now()->add($automation->time, $automation->unit)->timestamp * 1000;
                    } else if ($type == 'Completed' || $type == 'Upcoming') {
                        $due_date = Carbon::createFromTimestamp($attr['planned_date'])->add($automation->time, $automation->unit)->timestamp * 1000;
                    }
                } else if ($automation->condition == 'Harvesting') {
                    if ($type == 'Created') {
                        $due_date = Carbon::now()->add($automation->time, $automation->unit)->timestamp * 1000;
                    } else if ($type == 'Upcoming') {
                        $due_date = Carbon::createFromTimestamp($attr['planned_date_harvest'])->add($automation->time, $automation->unit)->timestamp * 1000;
                    }
                }

                $access = Account::find($attr['account_id'])->getAccUserHasPermission($automation->creator_id, 'line', $attr['line_id']);
                if ($automation->assigned_to && $access) {
                    $access = Account::find($attr['account_id'])->getAccUserHasPermission($automation->assigned_to, 'line', $attr['line_id']);
                }
            
                if (
                    $automation->condition == 'Seeding' || (
                    $automation->condition == 'Harvesting' && (
                        $type == 'Created' || $type == 'Upcoming'
                    ))
                ) {
                    if ($access) {
                        $task = Task::create([
                            'account_id' => $attr['account_id'],
                            'creator_id' => $automation->creator_id,
                            'farm_id' => $currentLine->farm_id,
                            'title' => $automation->title,
                            'content' => $automation->description,
                            'assigned_to' => $automation->assigned_to ? $automation->assigned_to : 0,
                            'line_id' => $attr['line_id'],
                            'due_date' => $due_date,
                        ]);
                    }
                }
            }
            // automation task end
            $currentLine->length = $attr['line_length'];
            $currentLine->update();
            if ($return) {
                return 1;
            } else {
                return response()->json(['status' => 'Success'], 201);
            }

        } else {

            if ($return) {
                return 1;
            } else {
                return response()->json(['status' => 'Error',
                                     'message' => 'Harvest already exists'], 400);
            }

        }
    }

    public function updateHarvest($attr)
    {
        HarvestGroup::where('id', $attr['harvest_group_id'])
            ->update([
                'name' => $attr['name'],
                'planned_date' => $attr['planned_date'],
                'seed_id' => $attr['seed_id'],
                'planned_date_harvest' => $attr['planned_date_harvest'],
                'planned_date_harvest_original' => $attr['planned_date_harvest'],
                'seed_id' => $attr['seed_id'],
                'density' => $attr['density'],
                'drop' => $attr['drop'],
                'floats' => $attr['floats'],
                'spacing' => $attr['spacing'],
                'submersion' => $attr['submersion'],
            ]);

        return response()->json(['status' => 'Success'], 200);
    }
}
