<?php

namespace App\Repositories\Line;

use App\Models\Assessment;
use App\Models\AssessmentPhoto;
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
use Image;
use File;

class AssessmentRepository implements AssessmentRepositoryInterface
{
    public function createAssessment($attr, $return=false)
    {
        $year = date("Y");
        $month = date("m");
        $uploadsDir = public_path('uploads/');
        $path = 'assessments/' . $year . '/' . $month . '/';
        $dir = $uploadsDir . $path;

        $images = $attr['images'];
        $collection = collect([]);

        foreach ($images as $image)
        {
            $name = time(). "-" . $image['name'];
            $contents = $image['thumbUrl'];

            if (!file_exists($dir)) {
                File::makeDirectory($dir, 0755, true);
            }

            Image::make($contents)->save($dir . $name);
            $collection->push($path . $name);
        }

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

            $assessPhotos = [];
            foreach ($collection as $image) {
                $assessPhotos[] = array(
                    'assessment_id' => $assessment->id,
                    'photo' => $image
                );
            }

            if (count($assessPhotos)) {
                AssessmentPhoto::insert($assessPhotos);
            }

            if ($return) {
                return $assessment->id;
            } else {
                return response()->json(['status' => 'Success'], 201);
            }
        }
    }

    public function getAssessments($attr)
    {
        $assessments = Assessment::where('line_id', $attr)->orderBy('created_at', 'DESC')->get();

        return AssessmentResource::collection($assessments, true);
    }
}
