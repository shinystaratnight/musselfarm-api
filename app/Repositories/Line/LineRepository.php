<?php

namespace App\Repositories\Line;

use App\Http\Resources\Line\LineResource;
use App\Models\HarvestGroup;
use App\Models\Line;
use App\Models\Farm;
use App\Models\LineArchive;
use App\Models\LineBudget;
use App\Models\Line\Assessment;
use Carbon\Carbon;

class LineRepository implements LineRepositoryInterface
{
    public function createLine($attr)
    {
        $line = Line::create([
            'farm_id' => $attr['farm_id'],
            'line_name' => $attr['line_name'],
            'length' => $attr['length'],
        ]);

        $date = Carbon::now();

        $startOfYear = $date->copy()->startOfYear()->timestamp;

        LineBudget::create([
            'line_id' => $line['id'],
            'start_budget' => $startOfYear,
            'length_budget' => $attr['length'],
            'length_actual' => $attr['length']
        ]);


        Farm::find($attr['farm_id'])->save((array)$line);

        return response()->json(['status' => 'Success', 'line_id' => $line->id], 200);
    }

    public function editLine($attr)
    {
        Line::where('id', $attr['line_id'])->update([
            'length' => $attr['length'],
            'line_name' => $attr['line_name'],
        ]);
    }
}
