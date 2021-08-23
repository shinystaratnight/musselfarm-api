<?php

namespace App\Repositories\Line;

use App\Models\Assessment;
use App\Models\ChartData;
use App\Models\HarvestGroup;
use App\Models\Automation;
use App\Models\Task;
use App\Models\Farm;
use App\Models\User;
use App\Models\Line;
use App\Models\Account;
use App\Http\Resources\Assessment\AssessmentResource;
use Carbon\Carbon;
use SimpleXLSXGen;
use App\Notifications\NewAssessment;

class AssessmentRepository implements AssessmentRepositoryInterface
{
    public function createAssessment($attr, $return=false)
    {
        // $average = ($attr['condition_min'] + $attr['condition_max']) / 2;
        $average = $attr['condition_avg'];

        $average = (int) round($average);

        $assessment = Assessment::create([
            'harvest_group_id' => $attr['harvest_group_id'],
            'color' => $attr['color'],
            'condition_min' => $attr['condition_min'] ? $attr['condition_min'] : 0,
            'condition_max' => $attr['condition_max'] ? $attr['condition_max'] : 0,
            'condition_avg' => $average,
            'blues' => $attr['blues'],
            'tones' => $attr['tones'],
            'condition_score' => $attr['condition_score'],
            'date_assessment' => $attr['date_assessment'],
            'planned_date_harvest' => $attr['planned_date_harvest'],
            'comment' => $attr['comment'],
        ]);

        if($assessment) {

            HarvestGroup::where(['id' => $attr['harvest_group_id'], 'harvest_complete_date' => 0])
                        ->update(['condition' => $assessment->condition_avg,
                                  'planned_date_harvest' => $assessment->planned_date_harvest,
                                  'color' => $assessment->color]);

            $harvest = HarvestGroup::where('id', $attr['harvest_group_id'])->first();
            $currentLine = Line::find($harvest->line_id);
            $farm_id = $currentLine->farm_id;

            // automation task start
            $automations = Automation::where([
                'condition' => 'Assessment',
                'account_id' => $attr['account_id']
            ])->get();

            foreach($automations as $automation) {
                
                $due_date = $due_date = Carbon::now()->add($automation->time, $automation->unit)->timestamp * 1000;

                $access = Account::find($attr['account_id'])->getAccUserHasPermission($automation->creator_id, 'line', $harvest->line_id);
                if ($automation->assigned_to && $access) {
                    $access = Account::find($attr['account_id'])->getAccUserHasPermission($automation->assigned_to, 'line', $harvest->line_id);
                }

                if ($access) {
                    $task = Task::create([
                        'account_id' => $attr['account_id'],
                        'creator_id' => $automation->creator_id,
                        'farm_id' => $farm_id,
                        'title' => $automation->title,
                        'content' => $automation->description,
                        'assigned_to' => $automation->assigned_to ? $automation->assigned_to : 0,
                        'line_id' => $harvest->line_id,
                        'due_date' => $due_date,
                    ]);
                }
            }
            // automation task end
            
            if ($return) {
                return 1;
            } else {
                return response()->json(['status' => 'Success'], 201);
            }
        }
    }

    public function createAssessmentFromApp($request)
    {
        $data = $request->input('data');
        $dataByUsers = array();
        $res = array();
        foreach ($data as $formData) {
            // $this->createAssessment($formData);
            if (!array_key_exists($formData['account_id'], $dataByUsers)) {
                $dataByUsers[$formData['account_id']] = array();
            }
            $dataByUsers[$formData['account_id']][] = $formData;
        }
        foreach($dataByUsers as $userAssessData) {
            $res[] = count($userAssessData);
            $harvest = HarvestGroup::where('id', $userAssessData[0]['harvest_group_id'])->first();
            $books = [
                ['SiteNo', 'line', 'area', 'Seed', 'mtrs', 'drop', 'dateSeeded', 'Owner1', 'AssessDate', 'ConditionScore'
                    , 'Colour', 'Min', 'Max', 'Avg', 'Blues', 'Tonnes', 'HarvestDate', 'Comment' ]
            ];
            foreach ($userAssessData as $assesData)  {
                $farm = Farm::find($assesData['farm_id']);
                $line = Line::find($assesData['line_id']);
                $owner = json_decode($farm['owner']);
                $books[] = [
                    $farm['farm_number'],
                    $line['line_name'],
                    $farm['area'],
                    $harvest->seed_id,
                    $harvest->line_length,
                    $harvest->drop,
                    Carbon::createFromTimestamp(intval($harvest->planned_date))->format('Y-m-d'),
                    $owner[0]->title,
                    Carbon::createFromTimestamp(intval($assesData['date_assessment']))->format('Y-m-d'),
                    $assesData['condition_score'],
                    $assesData['color'],
                    $assesData['condition_min'],
                    $assesData['condition_max'],
                    $assesData['condition_avg'],
                    $assesData['blues'],
                    $assesData['tones'],
                    Carbon::createFromTimestamp(intval($assesData['planned_date_harvest']))->format('Y-m-d'),
                    $assesData['comment'],
                ];
            }
            $fname = 'assess_' . round(microtime(true) * 1000) . '_' . $assesData['account_id'] . '.xlsx';
            $xlsx = SimpleXLSXGen::fromArray( $books );
            $xlsx->saveAs($fname);
            $acc = Account::find($assesData['account_id']);
            $user = User::find($acc['owner_id']);
            // $user->notify(new NewAssessment($fname));
        }
        // return response()->json(['status' => 'Success'], 201);
        return response()->json(['status' => 'Error', 'a' => $res], 201);
    }

    public function getAssessments($attr)
    {
        $assessments = Assessment::where('line_id', $attr)->orderBy('created_at', 'DESC')->get();

        return AssessmentResource::collection($assessments, true);
    }
}
