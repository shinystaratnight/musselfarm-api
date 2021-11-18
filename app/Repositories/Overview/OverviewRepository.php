<?php

namespace App\Repositories\Overview;


use App\Http\Resources\Overview\NextHarvestResource;
use App\Http\Resources\Overview\NextSeedingResource;
use App\Models\Assessment;
use App\Models\ChartData;
use App\Models\Account;
use App\Models\Farm;
use App\Models\HarvestGroup;
use App\Models\Line;
use App\Models\LineBudget;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class OverviewRepository implements OverviewRepositoryInterface
{

    public $year = null;
    public $from = null;
    public $to = null;
    public $month = null;
    public $previous15day = null;
    public $future15day = null;
    public $previousWeek = null;
    public $futureWeek = null;
    public $user_id = null;

    public function __construct()
    {
        $this->year = date("Y");

        $this->from = $this->getDate("-6 month");
        $this->to = $this->getDate("+6 month");
        $this->previous15day = $this->getDate("-15 days");
        $this->future15day = $this->getDate("+15 days");
        $this->previousWeek = $this->getDate("-3 days");
        $this->futureWeek = $this->getDate("+3 days");
    }

    public function getDate($numberOfDayMonthString)
    {
        $currentDate = date("Y-m-d"); //current date 
        return strtotime(date("Y-m-d", strtotime($currentDate)) . $numberOfDayMonthString);
    }

    public function plannedSeedingDate($acc_id = 0)
    {
        $nextHarvests = null;

        if ($acc_id != null) {

            $farms = Farm::where('account_id', $acc_id)->with('lines')->get();

            $lines = null;

            foreach ($farms as $f => $farm) {
                foreach ($farm->lines as $l => $line) {
                    $lines[] = $line->id;
                };
            }

            if ($lines != null) {
                $nextHarvests = HarvestGroup::whereIn('line_id', $lines)
                    ->where('planned_date', '>', Carbon::now()->timestamp)
                    ->orderBy('planned_date', 'DESC')
                    ->limit(3)
                    ->get();

                if (!empty($nextHarvests)) {

                    return NextSeedingResource::collection($nextHarvests->reverse());
                } else {

                    return [];
                }
            }
        }
    }

    public function farmReview($acc_id = 0)
    {
        if ($acc_id != null) {

            $farms = Farm::where('account_id', $acc_id);

            $farmsArea = $farms->sum('area');

            $lines = Line::whereIn('farm_id', $farms->pluck('id'));

            $linesLength = $lines->sum('length');

            $start = Carbon::now()->startOfYear()->timestamp;

            $totalTones = LineBudget::whereIn('line_id', $lines->pluck('id'))
                ->where('start_budget', $start)
                ->where('end_budget', 0)
                ->sum('planned_harvest_tones_actual');

            return response()->json([
                [
                    'name' => 'Total area',
                    'value' => !empty($farmsArea) ? $farmsArea : 0,
                    'unit' => 'h'
                ],
                [
                    'name' => 'Total length of lines',
                    'value' => !empty($linesLength) ? $linesLength : 0,
                    'unit' => 'm'
                ],
                [
                    'name' => 'Total harvest in current year',
                    'value' => !empty($totalTones) ? $totalTones : 0,
                    'unit' => 't'
                ]
            ], 200);
        }
    }

    public function accountDetail($acc_id = 0)
    {
        if ($acc_id != null) {

            $farms = Farm::where('account_id', $acc_id);

            $lines = Line::whereIn('farm_id', $farms->pluck('id'));

            $count_empty_lines = 0;
            foreach ($lines->get() as $k => $v) {

                $g = HarvestGroup::where('line_id', $v->id)->where('harvest_complete_date', 0)->first();

                if ($g == null) {
                    $count_empty_lines++;
                }
            }

            $start = Carbon::now()->startOfYear()->timestamp;

            $currentTotalIncome = LineBudget::whereIn('line_id', $lines->pluck('id'))
                ->where('start_budget', $start)
                ->where('end_budget', 0);

            $year = Carbon::now()->year;

            return response()->json([
                ['name' => 'Farms', 'value' => $farms->count()],
                [
                    'name' => 'Lines', 'value' => [
                        [
                            'label' => $year . ' Lines',
                            'value' => $lines->count()
                        ],
                        [
                            'label' => $year . ' Empty lines',
                            'value' => $count_empty_lines
                        ]
                    ]
                ],
                [
                    'name' => $year . ' Income', 'value' => [
                        [
                            'label' => $year . ' Income',
                            'value' => $currentTotalIncome->sum('budgeted_harvest_income_actual')
                        ],
                        [
                            'label' => $year . ' Budgeted income',
                            'value' => $currentTotalIncome->sum('budgeted_harvest_income')
                        ],
                    ]
                ],
                [
                    'name' => $year . ' Harvest tonnes', 'value' => [
                        [
                            'label' => $year . ' Harvest tonnes',
                            'value' => $currentTotalIncome->sum('planned_harvest_tones_actual')
                        ],
                        [
                            'label' => $year . ' Budgeted harvest tonnes',
                            'value' => $currentTotalIncome->sum('planned_harvest_tones')
                        ],
                    ]
                ],
            ], 200);
        }
    }

    public function plannedHarvestDate($acc_id = 0)
    {
        $nextHarvests = null;

        if ($acc_id != null) {

            $farms = Farm::where('account_id', $acc_id)->with('lines')->get();

            $lines = null;

            foreach ($farms as $f => $farm) {
                foreach ($farm->lines as $l => $line) {
                    $lines[] = $line->id;
                };
            }

            if ($lines != null) {
                $nextHarvests = HarvestGroup::whereIn('line_id', $lines)
                    ->where('planned_date_harvest', '>', Carbon::now()->timestamp)
                    ->orderBy('planned_date_harvest', 'DESC')
                    ->limit(3)
                    ->get();

                if (!empty($nextHarvests)) {

                    return NextHarvestResource::collection($nextHarvests->reverse());
                } else {

                    return [];
                }
            }
        }
    }

    public function farmsInfo($attr)
    {
        $farms = Account::find($attr['account_id'])->farms->where('id', $attr['farm_id']);

        $lines = Line::whereIn('farm_id', $farms->pluck('id'));

        $scy = Carbon::now()->startOfYear()->timestamp; // start current year

        $spy = Carbon::now()->subYear()->startOfYear()->timestamp; // start previous year

        $epy = $spy = Carbon::now()->subYear()->endOfYear()->timestamp; // end previous year

        $currentYear = LineBudget::whereIn('line_id', $lines->pluck('id'))
            ->where('start_budget', $scy)
            ->where('end_budget', 0);

        $previousYear = LineBudget::whereIn('line_id', $lines->pluck('id'))
            ->where('start_budget', $spy)
            ->where('end_budget', $epy);

        $year = Carbon::now()->year;

        return response()->json([
            [
                'name' => 'Harvested',
                'this_year' => $currentYear->sum('planned_harvest_tones_actual'),
                'last_year' => $previousYear->sum('planned_harvest_tones_actual'),
            ],
            //                ['name' => 'Seeded',
            //                    'this_year' => $currentYear->sum('planned_harvest_tones_actual'),
            //                    'last_year' => $previousYear->sum('planned_harvest_tones_actual'),
            //                ],
            [
                'name' => 'Earned',
                'this_year' => $currentYear->sum('budgeted_harvest_income_actual'),
                'last_year' => $previousYear->sum('budgeted_harvest_income_actual'),
            ],
        ], 200);
    }

    public function chartData($type = 'year',$acc_id = 0)
    {


        $grid = $this->createDateGrid($type);

        $assesst = null;
        $harv = null;
        $seed = null;

        $from = null;
        $to = null;

        switch ($type) {
            case "week":
                $from = $this->previousWeek;
                $to = $this->futureWeek;
                break;
            case "month":
                $from = $this->previous15day;
                $to = $this->future15day;
                break;
            case "year":
                $from = $this->from;
                $to = $this->to;
                break;
        }

        $assesst = $this->getAssessments($from, $to, $type,$acc_id);
        $harv = $this->getHarvests($from, $to, $type,$acc_id);
        $seed = $this->getSeeding($from, $to, $type,$acc_id);


        $assessments = ['name' => 'assessments'];
        $harvests = ['name' => 'harvest'];
        $seedings = ['name' => 'seedings'];

        foreach ($grid as $g => $m) {
            $assessments['values'][$g]['date'] = $m;
            $assessments['values'][$g]['count'] = 0;
            $assessments['values'][$g]['information'] = null;
            $assessments['values'][$g]['name'] = $type;

            $harvests['values'][$g]['date'] = $m;
            $harvests['values'][$g]['count'] = 0;
            $harvests['values'][$g]['information'] = null;
            $harvests['values'][$g]['name'] = $type;

            $seedings['values'][$g]['date'] = $m;
            $seedings['values'][$g]['count'] = 0;
            $seedings['values'][$g]['information'] = null;
            $seedings['values'][$g]['name'] = $type;
        }

        foreach ($assessments['values'] as $g => $a) {

            foreach ($assesst as $k => $assessment) {

                if ($k == $a['date']) {
                    $lines = [];

                    foreach ($assessment as $line) {
                        $lines[] = $line->group->lines->id;
                    }

                    $farms = [];

                    foreach ($assessment as $ff => $line) {
                        $farms[$ff]['farms'][] = $line->group->lines->farms->name;
                        $farms[$ff]['lines'][] = $line->group->lines->farms->lines->whereIn('id', $lines)->pluck('line_name');
                    }

                    $assessments['values'][$g]['date'] = $k;
                    $assessments['values'][$g]['count'] = $assessment->count();
                    $assessments['values'][$g]['information'] = $farms;
                    $assessments['values'][$g]['name'] = $type;
                }
            }
        }

        foreach ($harvests['values'] as $g => $a) {

            foreach ($harv as $k => $harvest) {

                if ($k == $a['date']) {
                    $lines = [];

                    foreach ($harvest as $ha) {
                        $lines[] = $ha->lines->id;
                    }

                    $farms = [];

                    foreach ($harvest as $ff => $ha) {
                        $farms[$ff]['farms'][] = $ha->lines->farms->name;
                        $farms[$ff]['lines'][] = $ha->lines->farms->lines->whereIn('id', $lines)->pluck('line_name');
                    }

                    $harvests['values'][$g]['date'] = $k;
                    $harvests['values'][$g]['count'] = $harvest->count();
                    $harvests['values'][$g]['information'] = $farms;
                    $harvests['values'][$g]['name'] = $type;
                }
            }
        }

        foreach ($seedings['values'] as $g => $a) {

            foreach ($seed as $k => $seeding) {

                if ($k == $a['date']) {
                    $lines = [];

                    foreach ($seeding as $se) {
                        $lines[] = $se->lines->id;
                    }

                    $farms = [];

                    foreach ($seeding as $ff => $se) {
                        $farms[$ff]['farms'][] = $se->lines->farms->name;
                        $farms[$ff]['lines'][] = $se->lines->farms->lines->whereIn('id', $lines)->pluck('line_name');
                    }

                    $seedings['values'][$g]['date'] = $k;
                    $seedings['values'][$g]['count'] = $seeding->count();
                    $seedings['values'][$g]['information'] = $farms;
                    $seedings['values'][$g]['name'] = $type;
                }
            }
        }

        return response()->json([
            $seedings,
            $assessments,
            $harvests,
        ], 200);
    }

    public function createDateGrid($type = 'year')
    {
        $gridArr = [];

        if ($type == 'month') {

            $day15 = $this->previous15day;

            $gridArr[] = date("Ymd", date($day15));
            for ($i = 1; $i <= 30; $i++) {
                $gridArr[$i] = date("Ymd", strtotime(date("Y-m-d", $day15) . "+$i days"));
            }
        } else if ($type == 'week') {

            $day3 = $this->previousWeek;

            $gridArr[] = date("Ymd", date($day3));
            for ($i = 1; $i <= 7; $i++) {
                $gridArr[$i] = date("Ymd", strtotime(date("Y-m-d", $day3) . "+$i days"));
            }
        } else if ($type == 'year') {

            $from = date("Y-m-d", $this->from);

            $gridArr[] = date("Ym", strtotime(date("Y-m", strtotime($from))));
            for ($i = 1; $i <= 11; $i++) {
                $gridArr[$i] = date("Ym", strtotime(date("Y-m", strtotime($from)) . "+$i months"));
            }
        }


        return $gridArr;
    }

    public function getAssessments($from, $to, $type = 'year',$acc_id = 0)
    {

        if ($acc_id) {

            $farms = Farm::where('account_id', $acc_id);

            $lines = Line::whereIn('farm_id', $farms->pluck('id'));

            $harvests = HarvestGroup::whereIn('line_id', $lines->pluck('id'));

            $m = function ($val) {
                return Carbon::now()->year . Carbon::parse($val->created_at)->format('m');
            };

            if ($type == "week" || $type == "month") {
                $m = function ($val) {
                    return Carbon::now()->year . Carbon::parse($val->created_at)->format('m') . Carbon::parse($val->created_at)->format('d');
                };
            }

            $assessments = Assessment::whereIn('harvest_group_id', $harvests->pluck('id'))
                ->whereBetween('planned_date_harvest', [$from, $to])
                ->with('group')
                ->get()
                ->groupBy($m);


            return $assessments;
        }
    }

    public function getHarvests($from, $to, $type = 'year',$acc_id = 0)
    {
  
        if ($acc_id) {

            $farms = Farm::where('account_id', $acc_id);

            $lines = Line::whereIn('farm_id', $farms->pluck('id'));

            $m = function ($val) {
                return Carbon::now()->year . Carbon::createFromTimestamp($val->planned_date_harvest)->format('m');
            };

            if ($type == "week" || $type == "month") {
                $m = function ($val) {
                    return Carbon::now()->year . Carbon::createFromTimestamp($val->planned_date_harvest)->format('m') . Carbon::createFromTimestamp($val->planned_date_harvest)->format('d');
                };
            }


            $harvests = HarvestGroup::whereIn('line_id', $lines->pluck('id'))
                ->whereBetween('planned_date_harvest', [$from, $to])
                ->with('lines')->get()
                ->groupBy($m);

            return $harvests;
        }
    }


    public function getSeeding($from, $to, $type = 'year',$acc_id = 0)
    {
    
        if ($acc_id) {
            
            $farms = Farm::where('account_id', $acc_id);
            $lines = Line::whereIn('farm_id', $farms->pluck('id'));

            $m = function ($val) {
                return Carbon::now()->year . Carbon::createFromTimestamp($val->planned_date)->format('m');
            };

            if ($type == "week" || $type == "month") {
                $m = function ($val) {
                    return Carbon::now()->year . Carbon::createFromTimestamp($val->planned_date)->format('m') . Carbon::createFromTimestamp($val->planned_date)->format('d');
                };
            }

            $seedings = HarvestGroup::whereIn('line_id', $lines->pluck('id'))
                ->whereBetween('planned_date', [$from, $to])
                ->with('lines')->get()
                ->groupBy($m);

            return $seedings;
        }
    }
}
