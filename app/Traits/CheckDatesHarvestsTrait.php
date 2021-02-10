<?php

namespace App\Traits;

use App\Models\HarvestGroup;
use Carbon\Carbon;

trait CheckDatesHarvestsTrait
{
    protected function checkStartSeeding($line_id, $start, $end)
    {
        $closures = HarvestGroup::where('line_id', $line_id)->where(function ($query) use ($start, $end) {

            $query->where(function ($q) use ($start, $end) {
                $q->where('planned_date', '>=', $start)
                    ->where('planned_date', '<', $end);

            })->orWhere(function ($q) use ($start, $end) {
                $q->where('planned_date', '<=', $start)
                    ->where('planned_date_harvest', '>', $end);

            })->orWhere(function ($q) use ($start, $end) {
                $q->where('planned_date_harvest', '>', $start)
                    ->where('planned_date_harvest', '<=', $end);

            })->orWhere(function ($q) use ($start, $end) {
                $q->where('planned_date', '>=', $start)
                    ->where('planned_date_harvest', '<=', $end);
            });

        })->count();

            if($closures == 0) {
                return true;
            } else {
                return false;
            }
    }
}
