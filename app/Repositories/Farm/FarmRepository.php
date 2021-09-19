<?php

namespace App\Repositories\Farm;

use App\Http\Resources\Farm\FarmResource;
use App\Models\Farm;
use App\Models\Line;
use App\Models\HarvestGroup;

class FarmRepository implements FarmRepositoryInterface
{
    public function createFarm($attr)
    {
        $user = auth()->user();
        $account = $user->getAccount($attr['account_id']);
        $farm = Farm::create([
            'account_id' => $attr['account_id'],
            'user_id' => $attr['user_id'],
            'name' => $attr['name'],
            'long' => $attr['long'],
            'lat' => $attr['lat'],
            'area' => $attr['area'],
            'farm_number' => $attr['farm_number'],
            'owner' => json_encode($attr['owner'])
        ]);

        $account->farms()->save($farm);

        return response()->json(['message' => 'Farm created'], 200);
    }

    public function farms($account_id)
    {
        $user = auth()->user();
        $farms = $user->getAccount($account_id)->getUserFarms($user->id);

        return FarmResource::collection($farms);
    }

    public function farmsByUser()
    {
        $user = auth()->user();
        $accs = $user->accounts;

        $farms = [];
        foreach ($accs as $acc) {
            $uac = $user->getAccount($acc->id);
            $acFarms = $uac->getUserFarms($user->id);
            $role = $uac->pivot->roles[0]['name'];
            $access = $uac->pivot->user_access;
            foreach ($acFarms as $acFarm) {
                $lines = Farm::with(['lines', 'lines.harvests'])->find($acFarm->id)->lines->toArray();
                $alines = array_filter($lines, function ($line) use($role, $access) {
                    if ($role == 'admin' || $role == 'owner') {
                        return true;
                    }
                    return in_array($line['id'], json_decode($access)->line_id);
                });
                $farms[] = [
                    'id' => $acFarm->id,
                    'name' => $acFarm->name,
                    'number' => $acFarm->farm_number,
                    'acc_id' => $acc->id,
                    'lines' => array_map(function($line) {
                        $harvest_id = '';
                        foreach ($line['harvests'] as $harvest) {
                            if ($harvest['harvest_complete_date'] == 0)
                                $harvest_id = $harvest['id'];
                        }
                        $latestAss = '';
                        if ($harvest_id) {
                            $ass = HarvestGroup::find($harvest_id)->assessments;
                            if (count($ass)) {
                                $latestAss = $ass[0];
                                foreach ($ass as $el) {
                                    if ($latestAss['date_assessment'] <= $el['date_assessment']) {
                                        $latestAss = $el;
                                    }
                                }
                            } else {
                                $latestAss = [
                                    "id" => "",
                                    "harvest_group_id" => "",
                                    "color" => "",
                                    "condition_min" => "",
                                    "condition_max" => "",
                                    "condition_avg" => "",
                                    "blues" => "",
                                    "tones" => "",
                                    "planned_date_harvest" => "",
                                    "comment" => "",
                                    "created_at" => "",
                                    "updated_at" => "",
                                    "deleted_at" => "",
                                    "date_assessment" => "",
                                    "condition_score" => ""
                                ];
                            }
                        }
                        return [
                            'id' => $line['id'],
                            'line_name' => $line['line_name'],
                            'harvest_id' => $harvest_id,
                            'last_assess' => $latestAss,
                            'status' => $harvest_id ? 'seeded' : 'empty'
                        ];
                    }, $alines),
                ];
            }
        }
        return response()->json($farms);
    }
}
