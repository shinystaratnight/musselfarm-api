<?php

namespace App\Repositories\Harvest;

use App\Models\ChartData;
use App\Models\HarvestGroup;
use App\Models\Line;
use App\Models\LineBudget;
use App\Traits\CheckDatesHarvestsTrait;
use Carbon\Carbon;

class HarvestRepository implements HarvestRepositoryInterface
{
    use CheckDatesHarvestsTrait;

    public function startHarvest($attr)
    {
        if($this->checkStartSeeding($attr['line_id'], $attr['planned_date'], $attr['planned_date_harvest'])) {

            HarvestGroup::create([
                'line_id' => $attr['line_id'],
                'name' => $attr['name'],
                'planned_date_harvest' => $attr['planned_date_harvest'],
                'planned_date_harvest_original' => $attr['planned_date_harvest'],
                'planned_date' => $attr['planned_date'],
                'planned_date_original' => $attr['planned_date'],
                'seed_id' => $attr['seed_id'],
                'density' => $attr['density'],
                'drop' => $attr['drop'],
                'floats' => $attr['floats'],
                'spacing' => $attr['spacing'],
                'submersion' => $attr['submersion'],
            ]);

            return response()->json(['status' => 'Success'], 200);

        } else {

            return response()->json(['status' => 'Error',
                                     'message' => 'The specified harvest period already exists'], 400);

        }
    }

    public function updateHarvest($attr)
    {
        HarvestGroup::where('id', $attr['harvest_group_id'])
            ->update([
                'name' => $attr['name'],
                'planned_date' => $attr['planned_date'],
                'seed_id' => $attr['seed_id'],
                'planned_date_harvest' => $attr['planned_date_harvest'],
                'planned_date_harvest_original' => $attr['planned_date_harvest'],
                'seed_id' => $attr['seed_id'],
                'density' => $attr['density'],
                'drop' => $attr['drop'],
                'floats' => $attr['floats'],
                'spacing' => $attr['spacing'],
                'submersion' => $attr['submersion'],
            ]);

        return response()->json(['status' => 'Success'], 200);
    }
}
