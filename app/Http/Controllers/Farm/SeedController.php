<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Http\Resources\Seed\SeedResource;
use App\Models\Seed;
use Illuminate\Http\Request;

class SeedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $seeds = Seed::all();

        return SeedResource::collection($seeds);
    }

    public function store(Request $request)
    {
        $seed = Seed::create(['name' => $request->name]);

        return new SeedResource($seed);
    }

    public function show(Seed $seed)
    {
        return new SeedResource($seed);
    }

    public function update(Request $request, Seed $seed)
    {
        $seed->update($request->toArray());

        return new SeedResource($seed);
    }

    public function destroy(Seed $seed)
    {
        $seed->delete();

        return response()->json(['message' => 'Delete successfully'], 200);
    }
}
