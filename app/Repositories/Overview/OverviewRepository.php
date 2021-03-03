<?php

namespace App\Repositories\Overview;


use App\Http\Resources\Overview\NextHarvestResource;
use App\Http\Resources\Overview\NextSeedingResource;
use App\Models\Assessment;
use App\Models\ChartData;
use App\Models\Farm;
use App\Models\HarvestGroup;
use App\Models\Line;
use App\Models\LineBudget;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class OverviewRepository implements OverviewRepositoryInterface
{
    public function plannedSeedingDate()
    {
        $user_id = null;

        $nextHarvests = null;

        if(auth()->user()->roles[0]['name'] == 'owner') {

            $user_id = auth()->user()->id;

        } elseif (in_array(auth()->user()->roles[0]['name'], ['admin', 'user'])) {

            $user_id = auth()->user()->getOwner();

        }

        if($user_id != null) {

            $farms = Farm::where('user_id', $user_id)->with('lines')->get();

            $lines = null;

            foreach ($farms as $f => $farm) {
                foreach ($farm->lines as $l => $line) {
                    $lines[] = $line->id;
                };
            }

            if($lines != null) {
                $nextHarvests = HarvestGroup::whereIn('line_id', $lines)
                                            ->where('planned_date', '>', Carbon::now()->timestamp)
                                            ->orderBy('planned_date', 'DESC')
                                            ->limit(3)
                                            ->get();

                if(!empty($nextHarvests)) {

                    return NextSeedingResource::collection($nextHarvests->reverse());

                } else {

                    return [];

                }
            }
        }
    }

    public function farmReview()
    {
        $user_id = null;

        if(auth()->user()->roles[0]['name'] == 'owner') {

            $user_id = auth()->user()->id;

        } elseif (in_array(auth()->user()->roles[0]['name'], ['admin', 'user'])) {

            $user_id = auth()->user()->getOwner();

        }

        if($user_id != null) {

            $farms = Farm::where('user_id', $user_id);

            $farmsArea = $farms->sum('area');

            $lines = Line::whereIn('farm_id', $farms->pluck('id'));

            $linesLength = $lines->sum('length');

            $start = Carbon::now()->startOfYear()->timestamp;

            $totalTones = LineBudget::whereIn('line_id', $lines->pluck('id'))
                                    ->where('start_budget', $start)
                                    ->where('end_budget', 0)
                                    ->sum('planned_harvest_tones_actual');

            return response()->json([['name' => 'Total area',
                                      'value' => !empty($farmsArea) ? $farmsArea : 0,
                                      'unit' => 'h'],
                                     ['name' => 'Total length of lines',
                                      'value' => !empty($linesLength) ? $linesLength : 0,
                                      'unit' => 'm'],
                                     ['name' => 'Total harvest in current year',
                                      'value' => !empty($totalTones) ? $totalTones : 0,
                                      'unit' => 't']], 200);
        }
    }

    public function accountDetail()
    {
        $user_id = null;

        if(auth()->user()->roles[0]['name'] == 'owner') {

            $user_id = auth()->user()->id;

        } elseif (in_array(auth()->user()->roles[0]['name'], ['admin', 'user'])) {

            $user_id = auth()->user()->getOwner();

        }

        if($user_id != null) {

            $farms = Farm::where('user_id', $user_id);

            $lines = Line::whereIn('farm_id', $farms->pluck('id'));

            $count_empty_lines = 0;
            foreach ($lines->get() as $k => $v) {

                $g = HarvestGroup::where('line_id', $v->id)->where('harvest_complete_date', 0)->first();

                if($g == null) {
                    $count_empty_lines++;
                }
            }

            $start = Carbon::now()->startOfYear()->timestamp;

            $currentTotalIncome = LineBudget::whereIn('line_id', $lines->pluck('id'))
                                            ->where('start_budget', $start)
                                            ->where('end_budget', 0);

            $year = Carbon::now()->year;

            return response()->json([['name' => 'Farms', 'value' => $farms->count()],
                                     ['name' => 'Lines', 'value' => [['label' => $year . ' Lines',
                                                                      'value' => $lines->count()],
                                                                     ['label' => $year . ' Empty lines',
                                                                      'value' => $count_empty_lines]
                                        ]
                                     ],
                                     ['name' => $year . ' Income', 'value' => [['label' => $year . ' Income',
                                                                                        'value' => $currentTotalIncome->sum('budgeted_harvest_income_actual')],
                                                                                       ['label' => $year . ' Budgeted income',
                                                                                        'value' => $currentTotalIncome->sum('budgeted_harvest_income')],
                                          ]
                                       ],
                                     ['name' => $year . ' Harvest tonnes', 'value' => [['label' => $year . ' Harvest tonnes',
                                                                                        'value' => $currentTotalIncome->sum('planned_harvest_tones_actual')],
                                                                                       ['label' => $year . ' Budgeted harvest tonnes',
                                                                                        'value' => $currentTotalIncome->sum('planned_harvest_tones')],
                                          ]
                                       ],
                                    ], 200);
        }
    }

    public function plannedHarvestDate()
    {
        $user_id = null;

        $nextHarvests = null;

        if(auth()->user()->roles[0]['name'] == 'owner') {

            $user_id = auth()->user()->id;

        } elseif (in_array(auth()->user()->roles[0]['name'], ['admin', 'user'])) {

            $user_id = auth()->user()->getOwner();

        }

        if($user_id != null) {

            $farms = Farm::where('user_id', $user_id)->with('lines')->get();

            $lines = null;

            foreach ($farms as $f => $farm) {
                foreach ($farm->lines as $l => $line) {
                    $lines[] = $line->id;
                };
            }

            if($lines != null) {
                $nextHarvests = HarvestGroup::whereIn('line_id', $lines)
                    ->where('planned_date_harvest', '>', Carbon::now()->timestamp)
                    ->orderBy('planned_date_harvest', 'DESC')
                    ->limit(3)
                    ->get();

                if(!empty($nextHarvests)) {

                    return NextHarvestResource::collection($nextHarvests->reverse());

                } else {

                    return [];

                }
            }
        }
    }

    public function farmsInfo($attr)
    {
        $user_id = null;

        if(auth()->user()->roles[0]['name'] == 'owner') {

            $user_id = auth()->user()->id;

        } elseif (in_array(auth()->user()->roles[0]['name'], ['admin', 'user'])) {

            $user_id = auth()->user()->getOwner();

        }

        if($user_id != null) {

            $farms = Farm::where('user_id', $user_id)->where('id', $attr['farm_id']);

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
                ['name' => 'Harvested',
                 'this_year' => $currentYear->sum('planned_harvest_tones_actual'),
                 'last_year' => $previousYear->sum('planned_harvest_tones_actual'),
                    ],
//                ['name' => 'Seeded',
//                    'this_year' => $currentYear->sum('planned_harvest_tones_actual'),
//                    'last_year' => $previousYear->sum('planned_harvest_tones_actual'),
//                ],
                ['name' => 'Earned',
                    'this_year' => $currentYear->sum('budgeted_harvest_income_actual'),
                    'last_year' => $previousYear->sum('budgeted_harvest_income_actual'),
                ],
            ], 200);

        }
    }

    public function chartData()
    {
        $grid = $this->createDateGrid();
        $assesst = $this->getAssessments();
        $harv = $this->getHarvests();
        $seed = $this->getSeeding();

        $assessments = ['name' => 'assessments'];
        $harvests = ['name' => 'harvest'];
        $seedings = ['name' => 'seedings'];

        foreach ($grid as $g => $m) {
            $assessments['values'][$g]['date'] = $m;
            $assessments['values'][$g]['count'] = 0;
            $assessments['values'][$g]['information'] = null;

            $harvests['values'][$g]['date'] = $m;
            $harvests['values'][$g]['count'] = 0;
            $harvests['values'][$g]['information'] = null;

            $seedings['values'][$g]['date'] = $m;
            $seedings['values'][$g]['count'] = 0;
            $seedings['values'][$g]['information'] = null;
        }

        foreach ($assessments['values'] as $g => $a) {

            foreach ($assesst as $k => $assessment) {

                if($k == $a['date']) {
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
                }
            }
        }

        foreach ($harvests['values'] as $g => $a) {

            foreach ($harv as $k => $harvest) {

                if($k == $a['date']) {
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
                }
            }
        }

        foreach ($seedings['values'] as $g => $a) {

            foreach ($seed as $k => $seeding) {

                if($k == $a['date']) {
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
                }
            }
        }

        return response()->json([
            $seedings,
            $assessments,
            $harvests,
        ], 200);
    }

    public function createDateGrid()
    {
        $gridArr = [];

        $currentYear = Carbon::now()->startOfYear()->format('m');

        $gridArr[] = intval(Carbon::now()->year . $currentYear);

        for($i = 1; $i <= 11; $i++) {

            $gridArr[$i] = intval(Carbon::now()->year . Carbon::now()->startOfYear()->addMonth($i)->format('m'));

        }

        return $gridArr;
    }

    public function getAssessments()
    {
        $user_id = null;

        if(auth()->user()->roles[0]['name'] == 'owner') {

            $user_id = auth()->user()->id;

        } elseif (in_array(auth()->user()->roles[0]['name'], ['admin', 'user'])) {

            $user_id = auth()->user()->getOwner();

        }

        if($user_id) {
            $farms = Farm::where('user_id', $user_id);

            $lines = Line::whereIn('farm_id', $farms->pluck('id'));

            $harvests = HarvestGroup::whereIn('line_id', $lines->pluck('id'));

            $assessments = Assessment::whereIn('harvest_group_id', $harvests->pluck('id'))->with('group')->get()
                                     ->groupBy(function($val) {
                                         return Carbon::now()->year . Carbon::parse($val->created_at)->format('m');
                                     });

            return $assessments;
        }
    }

    public function getHarvests()
    {
        $user_id = null;

        if(auth()->user()->roles[0]['name'] == 'owner') {

            $user_id = auth()->user()->id;

        } elseif (in_array(auth()->user()->roles[0]['name'], ['admin', 'user'])) {

            $user_id = auth()->user()->getOwner();

        }

        if($user_id) {

            $farms = Farm::where('user_id', $user_id);

            $lines = Line::whereIn('farm_id', $farms->pluck('id'));

            $from = Carbon::now()->startOfYear()->timestamp;

            $to = Carbon::now()->endOfYear()->timestamp;

            $harvests = HarvestGroup::whereIn('line_id', $lines->pluck('id'))
                                    ->whereBetween('planned_date_harvest',[$from, $to])
                                    ->with('lines')->get()
                                    ->groupBy(function($val) {
                                        return Carbon::now()->year . Carbon::createFromTimestamp($val->planned_date_harvest)->format('m');
                                    });

            return $harvests;
        }
    }

    public function getSeeding()
    {
        $user_id = null;

        if(auth()->user()->roles[0]['name'] == 'owner') {

            $user_id = auth()->user()->id;

        } elseif (in_array(auth()->user()->roles[0]['name'], ['admin', 'user'])) {

            $user_id = auth()->user()->getOwner();

        }

        if($user_id) {
            $farms = Farm::where('user_id', $user_id);

            $lines = Line::whereIn('farm_id', $farms->pluck('id'));

            $from = Carbon::now()->startOfYear()->timestamp;

            $to = Carbon::now()->endOfYear()->timestamp;

            $seedings = HarvestGroup::whereIn('line_id', $lines->pluck('id'))
                                    ->whereBetween('planned_date',[$from, $to])
                                    ->with('lines')->get()
                                    ->groupBy(function($val) {
                                        return Carbon::now()->year . Carbon::createFromTimestamp($val->planned_date)->format('m');
                                    });

            return $seedings;
        }
    }
}
