<?php

namespace App\Repositories\Line;

use App\Models\Assessment;
use App\Models\ChartData;
use App\Models\HarvestGroup;
use App\Models\Automation;
use App\Models\Task;
use App\Http\Resources\Assessment\AssessmentResource;
use App\Models\Line;
use Carbon\Carbon;

class AssessmentRepository implements AssessmentRepositoryInterface
{
    public function createAssessment($attr)
    {
        // $average = ($attr['condition_min'] + $attr['condition_max']) / 2;
        $average = $attr['condition_average'];

        $average = (int) round($average);

        $assessment = Assessment::create([
            'harvest_group_id' => $attr['harvest_group_id'],
            'color' => $attr['color'],
            'condition_min' => $attr['condition_min'],
            'condition_max' => $attr['condition_max'],
            'condition_avg' => $average,
            'blues' => $attr['blues'],
            'tones' => $attr['tones'],
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
                'creator_id' => auth()->user()->id,
                'condition' => 'Assessment',
            ])->get();

            foreach($automations as $automation) {
                
                $due_date = $due_date = Carbon::now()->addDays($automation->time)->timestamp * 1000;

                $task = Task::create([
                    'creator_id' => auth()->user()->id,
                    'farm_id' => $farm_id,
                    'title' => $automation->title,
                    'content' => $automation->description,
                    'charger_id' => 0,
                    'line_id' => $harvest->line_id,
                    'due_date' => $due_date,
                ]);
            }
            // automation task end
            
            return response()->json(['status' => 'Success'], 201);
        }
    }

    public function getAssessments($attr)
    {
        $assessments = Assessment::where('line_id', $attr)->orderBy('created_at', 'DESC')->get();

        return AssessmentResource::collection($assessments);
    }
}
