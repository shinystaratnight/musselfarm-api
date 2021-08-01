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
        $user = auth()->user();
        $account = $user->getAccount($attr['account_id']);
        $farm = Farm::create([
            'account_id' => $attr['account_id'],
            'user_id' => $attr['user_id'],
            'name' => $attr['name'],
            'long' => $attr['long'],
            'lat' => $attr['lat'],
            'area' => $attr['area'],
            'farm_number' => $attr['farm_number'],
            'owner' => json_encode($attr['owner'])
        ]);

        $account->farms()->save($farm);

        return response()->json(['message' => 'Farm created'], 200);

        // $farm = Farm::create([
        //     'user_id' => $attr['user_id'],
        //     'name' => $attr['name'],
        //     'long' => $attr['long'],
        //     'lat' => $attr['lat'],
        //     'area' => $attr['area'],
        //     'farm_number' => $attr['farm_number'],
        //     'owner' => json_encode($attr['owner'])
        // ]);

        // if(auth()->user()->roles[0]['name'] === 'owner') {

        //     auth()->user()->farms()->attach($farm->id);

        // } else {

        //     auth()->user()->farms()->attach($farm->id);

        //     auth()->user()->getOwner()->farms()->attach($farm->id);

        // }

        // return response()->json(['message' => 'Farm created'], 200);
    }

    public function farms($account_id)
    {
        $user = auth()->user();
        $farms = $user->getAccount($account_id)->getUserFarms($user->id);
        
        // $farms = Farm::whereHas('users', function($q) use ($user) {
        //    $q->where('user_id', '=', $user);
        // })->get();

        return FarmResource::collection($farms);
    }
}
