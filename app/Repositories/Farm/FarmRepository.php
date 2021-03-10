<?php

namespace App\Repositories\Farm;

use App\Http\Resources\Farm\FarmResource;
use App\Models\Farm;
use App\Models\Line;
use App\Models\HarvestGroup;

class FarmRepository implements FarmRepositoryInterface
{
    public function createFarm($attr)
    {
        $farm = Farm::create([
            'user_id' => $attr['user_id'],
            'name' => $attr['name'],
            'long' => $attr['long'],
            'lat' => $attr['lat'],
            'area' => $attr['area'],
            'farm_number' => $attr['farm_number'],
            'owner' => json_encode($attr['owner'])
        ]);

        if(auth()->user()->roles[0]['name'] === 'owner') {

            auth()->user()->farms()->attach($farm->id);

        } else {

            auth()->user()->farms()->attach($farm->id);

            auth()->user()->getOwner()->farms()->attach($farm->id);

        }

        return response()->json(['message' => 'Farm created'], 200);
    }

    public function farms($user)
    {
        $farms = Farm::whereHas('users', function($q) use ($user) {
           $q->where('user_id', '=', $user);
        })->get();

        return FarmResource::collection($farms);
    }
}
