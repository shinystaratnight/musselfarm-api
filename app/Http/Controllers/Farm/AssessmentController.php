<?php

namespace App\Http\Controllers\Farm;

use App\Models\Assessment;
use App\Models\HarvestGroup;
use App\Models\Line;
use App\Http\Requests\Assessment\CreateAssessmentRequest;
use App\Http\Requests\Assessment\UpdateAssessmentRequest;
use App\Repositories\Line\AssessmentRepositoryInterface as AssessmentLine;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class AssessmentController extends Controller
{
    private $assessmentRepo;

    public function __construct(AssessmentLine $assessmentLine)
    {
        $this->assessmentRepo = $assessmentLine;
    }

    public function index()
    {
        return $this->assessmentRepo->getAssessments(request()->line_id);
    }

    public function show()
    {
        return $this->assessmentRepo->getAssessment();
    }

    public function store(CreateAssessmentRequest $request)
    {
        $attr = $request->validated();

        return $this->assessmentRepo->createAssessment($attr);
    }

    public function update(UpdateAssessmentRequest $request, Assessment $assessment)
    {
        $assessment->update($request->validated());

        $avg = ($assessment['condition_min'] + $assessment['condition_max']) / 2;

        $avg = (int) round($avg);

        $assessment->update(['condition_avg' => $avg]);

        $lastCreated = Assessment::where('harvest_group_id', $assessment['harvest_group_id'])->orderBy('created_at','DESC')->first();

        if($lastCreated->id == $assessment['id']) {

            HarvestGroup::where(['id' => $lastCreated->harvest_group_id, 'harvest_complete_date' => 0])
                ->update(['color' => $assessment['color'],
                          'condition' => $assessment['condition_avg'],
                          'planned_date_harvest' => $assessment['planned_date_harvest']]);
        }

        return response()->json(['message' => 'Update successfully'], 200);
    }

    public function destroy(Assessment $assessment)
    {
        $deletedAssessment = $assessment;

        $assessment->delete();

        $lastAssessment = Assessment::where('harvest_group_id', $deletedAssessment->harvest_group_id)->with(["harvests" => function($q){
            $q->where('harvest_complete_date', 0);
        }])->orderBy('created_at', 'DESC')->first();

        if($lastAssessment) {
            HarvestGroup::where(['id' => $deletedAssessment->harvest_group_id, 'harvest_complete_date' => 0])
                        ->update(['condition' => $lastAssessment->condition_avg,
                                  'planned_date_harvest' => $lastAssessment->planned_date_harvest,
                                  'color' => $lastAssessment->color]);
        } else {
            HarvestGroup::where(['id' => $deletedAssessment->harvest_group_id, 'harvest_complete_date' => 0])
                ->update(['condition' => '0', 'color' => null]);
        }

        return response()->json(['message' => 'Delete successfully'], 200);
    }
}
