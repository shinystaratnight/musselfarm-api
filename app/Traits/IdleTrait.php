<?php

namespace App\Traits;

use App\Models\HarvestGroup;
use App\Models\Line;
use Carbon\Carbon;

trait IdleTrait
{
    public function idleAvgForFarm($farm_id)
    {
        if($farm_id) {
            return 0;
//            $now = Carbon::now();
//            $yearNow = Carbon::now()->startOfYear();
//            $firstDayOfYear = Carbon::now()->firstOfYear()->timestamp;
//            $lastDayOfYear = Carbon::now()->lastOfYear()->timestamp;
//            $currentYearDays = $yearNow->diffInDays($now);
//
//            $lines = Line::where('farm_id', $farm_id)->pluck('id');
//
//            if($lines->isNotEmpty()) {
//
//                $harvests = HarvestGroup::whereIn('line_id', [44])
//                    ->where('planned_date', '>=', $firstDayOfYear)
//                    ->where('planned_date_harvest', '<=', $lastDayOfYear)
//                    ->get();
//
//                if($harvests->isNotEmpty()) {
//
//                    $loadArr = [];
//
//                    foreach ($harvests as $k => $harvest) {
//                        if (($harvest['planned_date'] <= $firstDayOfYear) && ($harvest['planned_date_harvest'] >= $firstDayOfYear)) {
//                            $range = Carbon::createFromTimestamp($harvest['planned_date_harvest'])->diffInDays(Carbon::createFromTimestamp($firstDayOfYear));
//
//                            $loadArr[] = $range;
//                        } else {
//                            $range = Carbon::createFromTimestamp($harvest['planned_date'])->diffInDays(Carbon::createFromTimestamp($harvest['planned_date_harvest']));
//
//                            $loadArr[] = $range;
//                        }
//                    }
//
//                    $idleDays = $currentYearDays - array_sum($loadArr);
//
//                    if ($idleDays > 0) {
//
//                        return intval(round($idleDays / count($loadArr)));
//
//                    }
//                }
//
//            } else {
//
//                return 0;
//
            }
//            * * * * * * * * * * * *

//            $now = Carbon::now();
//
//            $yearNow = Carbon::now()->startOfYear();
//            $currentYearDays = $yearNow->diffInDays($now);
//            $firstDayOfYear = Carbon::now()->firstOfYear()->timestamp;
//            $lastDayOfYear = Carbon::now()->lastOfYear()->timestamp;
//
//            $harvests = HarvestGroup::whereIn('line_id', $lines)
//                                    ->where([
//                                        ['planned_date', '>=', $firstDayOfYear],
//                                        ['planned_date_harvest', '<=', $lastDayOfYear],
//                                    ])
//                                    ->orWhere([
//                                        ['planned_date', '<=', $firstDayOfYear],
//                                        ['planned_date_harvest', '>=', $firstDayOfYear]
//                                    ])
//                                    ->get();
//
//            $lineQuantity = $lines->count();
//
//            if($lineQuantity > 0) {
//
//                $harvestTotalDays = 0;
//
//                if ($lines->isNotEmpty()) {
//
//                    foreach ($harvests as $key => $harvest) {
//                        if (($harvest['planned_date'] <= $firstDayOfYear) && ($harvest['planned_date_harvest'] >= $firstDayOfYear)) {
//                            $range = $currentYearDays - Carbon::createFromTimestamp($harvest['planned_date_harvest'])->diffInDays(Carbon::createFromTimestamp($firstDayOfYear));
//
//                            $harvestTotalDays += $range;
//                        } else {
//                            $range = $currentYearDays - Carbon::createFromTimestamp($harvest['planned_date'])->diffInDays(Carbon::createFromTimestamp($harvest['planned_date_harvest']));
//
//                            $harvestTotalDays += $range;
//                        }
//                    }
//
//                    if ($harvestTotalDays > 0) {
//                        return intval(round($harvestTotalDays / $lineQuantity, 0));
//                    } else {
//                        return 0;
//                    }
//                } else {
//                    return 0;
//                }
//            } else {
//                return 0;
//            }
        } 
//        else {
//            return 0;
//        }
//    }

    public function idleCurrentForLine($line_id = null)
    {
        if($line_id != null) {

            $existHarvest = HarvestGroup::where('line_id', $line_id)
                                        ->where('harvest_complete_date', 0)
                                        ->first();

            if ($existHarvest == null) {

                $harvest = HarvestGroup::where('line_id', $line_id)
                                       ->where('harvest_complete_date', '!=', 0)
                                       ->orderBy('harvest_complete_date', 'DESC')
                                       ->first();

                if($harvest != null) {

//                    if ($harvest->harvest_complete_date != 0 && $harvest->harvest_complete_date <= Carbon::now()->timestamp) {

                        $now = Carbon::now();

                        $diff = Carbon::createFromTimestamp($harvest->harvest_complete_date)->diffInDays($now);

                        return $diff;

//                    } else {
//
//                        return null;
//
//                    }
                } else {

                    return null;

                }

            } else {

                return null;

            }
        }
    }
}
