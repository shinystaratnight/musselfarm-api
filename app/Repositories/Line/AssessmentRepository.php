<?php

namespace App\Repositories\Line;

use App\Models\Assessment;
use App\Models\ChartData;
use App\Models\HarvestGroup;
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

            return response()->json(['status' => 'Success'], 201);
        }
    }

    public function getAssessments($attr)
    {
        $assessments = Assessment::where('line_id', $attr)->orderBy('created_at', 'DESC')->get();

        return AssessmentResource::collection($assessments);
    }
}
