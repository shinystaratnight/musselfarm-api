<?php

namespace App\Http\Controllers\Farm;

use App\Models\Farm;
use App\Models\Line;
use App\Models\HarvestGroup;
use App\Models\Account;
use App\Models\User;
use App\Models\Season;
use App\Http\Requests\Farm\FarmRequest;
use App\Http\Requests\Farm\UpdateFarmRequest;
use App\Http\Resources\Farm\FarmResource;
use App\Http\Controllers\Controller;
use App\Repositories\Farm\FarmRepositoryInterface as MarineFarm;
use App\Repositories\Line\AssessmentRepositoryInterface as AssessmentLine;
use App\Repositories\Harvest\HarvestRepositoryInterface as Harvest;
use Illuminate\Http\Request;
use Carbon\Carbon;
use SimpleXLSXGen;
use App\Notifications\NewAssessment;

class FarmController extends Controller
{
    private $farmRepo;
    private $assessmentRepo;
    private $harvestRepo;

    public function __construct(MarineFarm $farming, AssessmentLine $assessmentLine, Harvest $harvest)
    {
        $this->farmRepo = $farming;
        $this->assessmentRepo = $assessmentLine;
        $this->harvestRepo = $harvest;
    }

    public function index()
    {
//        return $this->farmRepo->farms(auth()->user()->id);
    }

    public function show(Request $request, Farm $farm)
    {
        $this->authorize('show', [
            $farm,
            $request->input('account_id')
        ]);

        return new FarmResource($farm);
    }

    public function store(FarmRequest $request)
    {
        $attr = $request->validated();

        return $this->farmRepo->createFarm($attr);
    }

    public function update(UpdateFarmRequest $request, Farm $farm)
    {
        $this->authorize('update', [
            $farm,
            $request->input('account_id')
        ]);
        
        $farm->update($request->validated());

        return response()->json(['message' => 'Update completed'], 200);
    }

    public function allFarms(Request $request)
    {
        return $this->farmRepo->farms($request->input('account_id'));
    }

    public function allFarmsByUser(Request $request)
    {
        return $this->farmRepo->farmsByUser();
    }

    public function destroy(Request $request, Farm $farm)
    {
        $this->authorize('update', [
            $farm,
            $request->input('account_id')
        ]);

        $deletedFarm = $farm;

        $farm->delete();

        return response()->json(['message' => 'Success'], 200);
    }

    public function syncDataFromApp(Request $request)
    {
        $data = (array)$request->input('data');
        $emailNotify = $request->input('email');
        $dataByUsers = array();
        $res = array();
        foreach ($data as $formData) {
            if ($formData['type'] == 'assessment') {
                if (intval($formData['harvest_group_id']) == -1) {
                    $hv = HarvestGroup::where('line_id', $formData['line_id'])->where('harvest_complete_date', 0)->first();
                    if ($hv) {
                        $formData['harvest_group_id'] = $hv->id;
                    }
                }
                $this->assessmentRepo->createAssessment($formData, true);
                if (!array_key_exists($formData['account_id'], $dataByUsers)) {
                    $dataByUsers[$formData['account_id']] = array();
                }
                $dataByUsers[$formData['account_id']][] = $formData;
            } else if ($formData['type'] == 'seeding') {
                $season = Season::where('account_id', $formData['account_id'])->where('season_name', $formData['name'])->first();
                if ($season) {
                    $formData['name'] = $season->id;
                } else {
                    $season = Season::create([
                        'account_id' => $formData['account_id'],
                        'user_id' => auth()->user()->id,
                        'season_name' => $formData['name'],
                    ]);
                    $formData['name'] = $season->id;
                }
                $this->harvestRepo->startHarvest($formData, true);
            }
        }
        if ($emailNotify) {
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
                $user->notify(new NewAssessment($fname));
            }
        }
        return response()->json(['status' => 'Success'], 201);
    }
}
