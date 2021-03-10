<?php

namespace App\Repositories\Line;

use App\Http\Resources\Line\LineResource;
use App\Models\HarvestGroup;
use App\Models\Line;
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

        if(auth()->user()->roles[0]['name'] === 'owner') {

            auth()->user()->lines()->attach($line->id);

        } else {

            auth()->user()->lines()->attach($line->id);

            auth()->user()->getOwner()->lines()->attach($line->id);
        }

        return response()->json(['status' => 'Success', 'line_id' => $line->id], 200);
    }

    public function getLinesByFarmId()
    {
        $lines = Line::has('users', '=', auth()->user()->id)->with('harvests')->get();

        return LineResource::collection($lines);
    }

    public function editLine($attr)
    {
        Line::where('id', $attr['line_id'])->update([
            'length' => $attr['length'],
            'line_name' => $attr['line_name'],
        ]);
    }
}
