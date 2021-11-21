<?php

namespace App\Http\Controllers\Farm;

use App\Models\Farm;
use App\Models\Line;
use App\Models\HarvestGroup;
use App\Models\Account;
use App\Models\Task;
use App\Models\User;
use App\Models\AssessmentPhoto;
use App\Models\Season;
use App\Models\Automation;
use App\Models\LineArchive;
use App\Models\LineBudget;
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
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Farm\LineSortingRequest;

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
        //        return $this->farmRepo->farms( auth()->user()->id );
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

    public function createDirectory($dir)
    {
        if (!is_dir($dir)) {
            return Storage::makeDirectory($dir);
        }
    }

    public function syncDataFromApp(Request $request)
    {
        $year = date("Y");
        $month = date("m");

        $dir = 'uploads/';
        $this->createDirectory($dir);

        $uploadingDir = $dir . 'assessments/';
        $this->createDirectory($uploadingDir);

        $currentYearDir = $uploadingDir . $year . '/';
        $this->createDirectory($currentYearDir);

        $currentMonthDir = $currentYearDir . $month . '/';
        $this->createDirectory($currentMonthDir);

        if ($request->hasFile('file')) {
            $files = $request->file('file');
            foreach ($files as $file) {
                $file->move($currentMonthDir, $file->getClientOriginalName());
            }
        }

        $emailNotify = $request->input('email');
        $data = (array)json_decode($request->input('data'));
        $dataByUsers = array();
        $res = array();

        foreach ($data as $frmData) {
            // $formData = ( array )json_decode( $frmData )[0];
            $formData = (array)$frmData;
            // For mobile

            if ($formData['type'] == 'assessment') {
                if (intval($formData['harvest_group_id']) <= 0) {
                    $hv = HarvestGroup::where('line_id', $formData['line_id'])->where('harvest_complete_date', 0)->first();
                    if ($hv) {
                        $formData['harvest_group_id'] = $hv->id;
                    }
                }

                $assessId = $this->assessmentRepo->createAssessment($formData, true);
                $assessImages = (array)$formData['images'];
                $assessPhotos = [];
                foreach ($assessImages as $assessImage) {
                    $assessPhotos[] = array(
                        'assessment_id' => $assessId,
                        'photo' => $assessImage
                    );
                }
                if (count($assessPhotos)) {
                    AssessmentPhoto::insert($assessPhotos);
                }
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
            } else if ($formData['type'] == 'harvest') {
                $this->doHarvest($formData);
            }
        }
        if ($emailNotify == 'true') {
            foreach ($dataByUsers as $userAssessData) {
                $res[] = count($userAssessData);
                $harvest = HarvestGroup::where('id', $userAssessData[0]['harvest_group_id'])->first();
                $books = [
                    [
                        'SiteNo', 'line', 'area', 'Seed', 'mtrs', 'drop', 'dateSeeded', 'Owner1', 'AssessDate', 'ConditionScore', 'Colour', 'Min', 'Max', 'Avg', 'Blues', 'Tonnes', 'HarvestDate', 'Comment'
                    ]
                ];
                foreach ($userAssessData as $assesData) {
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
                $xlsx = SimpleXLSXGen::fromArray($books);
                $xlsx->saveAs($fname);
                $acc = Account::find($assesData['account_id']);
                $user = User::find($acc['owner_id']);
                $user->notify(new NewAssessment($fname));
            }
        }
        return response()->json(['status' => 'Success'], 201);
    }

    public function doHarvest($attr)
    {

        $harvest = null;
        if (intval($attr['harvest_group_id']) > 0) {
            $harvest = HarvestGroup::where('id', $attr['harvest_group_id'])->first();
        } else {
            $harvest = HarvestGroup::where('line_id', $attr['line_id'])->where('harvest_complete_date', 0)->first();
        }
        $harvest->planned_date_harvest = $attr['date'];

        $currentLine = Line::find($harvest->line_id);

        $requestHarvestDate = Carbon::createFromTimestamp($harvest->planned_date_harvest)->year;

        $currentYear = Carbon::now()->year;

        // automation task start
        $automations = Automation::where([
            'condition' => 'Harvesting',
            'action' => 'Completed',
            'account_id' => $attr['account_id']
        ])->get();

        foreach ($automations as $automation) {

            $due_date = Carbon::createFromTimestamp($attr['date'])->add($automation->time, $automation->unit)->timestamp * 1000;

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

        if ($currentYear == $requestHarvestDate) {

            $completedHarvest = HarvestGroup::where(['id' => $harvest->id, 'harvest_complete_date' => 0])
                ->update([
                    'harvest_complete_date' => $harvest->planned_date_harvest,
                    'planned_date_harvest' => $harvest->planned_date_harvest,
                    'company' => $attr['company'],
                    'vessel' => $attr['vessel'],
                    'harvest_number' => $attr['harvest_number'],
                    'number_of_bags' => $attr['number_of_bags'],
                    'tag_color' => $attr['tag_color'],
                    'port_of_unload' => $attr['port_of_unload'],
                    'crop_owner' => $attr['crop_owner'],
                    'growing_area' => $attr['growing_area'],
                    'delivered_to' => $attr['delivered_to'],
                    'packhouse' => $attr['packhouse'],
                    'start_time' => $attr['start_time'],
                    'finish_time' => $attr['finish_time'],
                    'bags_clean' => $attr['bags_clean'],
                    'area_open_for_harvest' => $attr['area_open_for_harvest'],
                    'trucks_booked' => $attr['trucks_booked'],
                    'more_clean_bags_on_truck' => $attr['more_clean_bags_on_truck'],
                    'shell_length' => $attr['shell_length'],
                    'shell_condition' => $attr['shell_condition'],
                    'mussels' => $attr['mussels'],
                    'meat_yield' => $attr['meat_yield'],
                    'blues' => $attr['blues'],
                    'marine_waste' => $attr['marine_waste'],
                    'backbone_ok' => $attr['backbone_ok'],
                    'backbone_replace' => $attr['backbone_replace'],
                    'lights_ids_in_place' => $attr['lights_ids_in_place'],
                    'flotation_on_farm' => $attr['flotation_on_farm'],
                    'number_of_rope_bags' => $attr['number_of_rope_bags'],
                    'product_left_on_line' => $attr['product_left_on_line'],
                    'harvestor_name' => $attr['harvestor_name'],
                    'signature' => $attr['signature'],
                    'comments' => $attr['comments']
                ]);

            if ($completedHarvest) {

                $archiveData = HarvestGroup::where('id', $harvest->id)->with('lines')->first();

                $startOfYear = Carbon::parse('first day of January ' . $requestHarvestDate)->timestamp;

                $budget = LineBudget::where('line_id', $archiveData->line_id)->where('start_budget', $startOfYear)->first();

                $budget->planned_harvest_tones_actual += floatval($attr['number_of_bags']);

                $budget->budgeted_harvest_income_actual += floatval($attr['budgeted_harvest_income_actual']);

                $budget->save();

                $profitPerMeterCalculation = $attr['budgeted_harvest_income_actual'] / $budget->length_actual;

                $archiveData->profit_per_meter = round($profitPerMeterCalculation, 2);

                $archiveData->save();

                LineArchive::create([
                    'harvest_group_id' => $harvest->id,
                    'length' => $archiveData->line_length,
                    'planned_date_harvest' => $archiveData->planned_date_harvest,
                    'planned_date_harvest_original' => $archiveData->planned_date_harvest,
                    'planned_date' => $archiveData->planned_date,
                    'seed_id' => $archiveData->seed_id,
                    'condition' => $archiveData->condition,
                    'profit_per_meter' => $archiveData->profit_per_meter
                ]);
            }
        } else {

            $startOfYear = Carbon::parse('first day of January ' . $requestHarvestDate)->timestamp;

            $endOfYear = Carbon::parse('last day of December ' . $requestHarvestDate)->timestamp;

            $budget = LineBudget::where([
                'line_id' => $harvest->line_id,
                'start_budget' => $startOfYear,
                'end_budget' => $endOfYear
            ])->first();

            if (!$budget) {
                $budget = LineBudget::create([
                    'line_id' => $harvest->line_id,
                    'start_budget' => $startOfYear,
                    'end_budget' => $endOfYear,
                    'length_actual' => $currentLine->length,
                    'length_budget' => $currentLine->length,
                ]);
            }

            $completedHarvest = HarvestGroup::where(['id' => $harvest->id, 'harvest_complete_date' => 0])
                ->update([
                    'harvest_complete_date' => $harvest->planned_date_harvest,
                    'planned_date_harvest' => $harvest->planned_date_harvest,
                    'company' => $attr['company'],
                    'vessel' => $attr['vessel'],
                    'harvest_number' => $attr['harvest_number'],
                    'number_of_bags' => $attr['number_of_bags'],
                    'tag_color' => $attr['tag_color'],
                    'port_of_unload' => $attr['port_of_unload'],
                    'crop_owner' => $attr['crop_owner'],
                    'growing_area' => $attr['growing_area'],
                    'delivered_to' => $attr['delivered_to'],
                    'packhouse' => $attr['packhouse'],
                    'start_time' => $attr['start_time'],
                    'finish_time' => $attr['finish_time'],
                    'bags_clean' => $attr['bags_clean'],
                    'area_open_for_harvest' => $attr['area_open_for_harvest'],
                    'trucks_booked' => $attr['trucks_booked'],
                    'more_clean_bags_on_truck' => $attr['more_clean_bags_on_truck'],
                    'shell_length' => $attr['shell_length'],
                    'shell_condition' => $attr['shell_condition'],
                    'mussels' => $attr['mussels'],
                    'meat_yield' => $attr['meat_yield'],
                    'blues' => $attr['blues'],
                    'marine_waste' => $attr['marine_waste'],
                    'backbone_ok' => $attr['backbone_ok'],
                    'backbone_replace' => $attr['backbone_replace'],
                    'lights_ids_in_place' => $attr['lights_ids_in_place'],
                    'flotation_on_farm' => $attr['flotation_on_farm'],
                    'number_of_rope_bags' => $attr['number_of_rope_bags'],
                    'product_left_on_line' => $attr['product_left_on_line'],
                    'harvestor_name' => $attr['harvestor_name'],
                    'signature' => $attr['signature'],
                    'comments' => $attr['comments']
                ]);

            if ($completedHarvest) {

                $archiveData = HarvestGroup::where('id', $harvest->id)->with('lines')->first();

                $budget->planned_harvest_tones_actual += floatval($attr['number_of_bags']);

                $budget->budgeted_harvest_income_actual += floatval($attr['budgeted_harvest_income_actual']);

                $budget->save();

                $profitPerMeterCalculation = $attr['budgeted_harvest_income_actual'] / $budget->length_actual;

                $archiveData->profit_per_meter = round($profitPerMeterCalculation, 2);

                $archiveData->save();

                LineArchive::create([
                    'harvest_group_id' => $harvest->id,
                    'length' => $archiveData->line_length,
                    'planned_date_harvest' => $archiveData->planned_date_harvest,
                    'planned_date_harvest_original' => $archiveData->planned_date_harvest,
                    'planned_date' => $archiveData->planned_date,
                    'seed_id' => $archiveData->seed_id,
                    'condition' => $archiveData->condition,
                    'profit_per_meter' => $archiveData->profit_per_meter
                ]);
            }
        }
    }

    public function lineSorting(LineSortingRequest $request)
    {
        return $this->farmRepo->lineSorting($request);
    }

    public function getLineSorting(\Illuminate\Http\Request $request)
    {
        return $this->farmRepo->getLineSorting($request);
    }
}
