<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Http\Requests\Season\SeasonRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SeasonController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $seasons = Season::where('user_id', $user->id)->where('account_id', $request->input('account_id'))->get();

        return response()->json([
            'status' => 'success',
            'data' => $seasons,
        ], 200);
    }

    public function store(SeasonRequest $request)
    {
        $attr = $request->validated();

        $season = Season::create([
            'account_id' => $attr['account_id'],
            'user_id' => auth()->user()->id,
            'season_name' => $attr['name'],
        ]);

        return response()->json(['status' => 'success', 'id' => $season->id], 200);
    }
}
