<?php

namespace App\Repositories\Farm;

use App\Http\Resources\Farm\FarmResource;
use App\Models\Farm;
use App\Models\Line;
use App\Models\HarvestGroup;
use App\Models\UserSettings;
use Illuminate\Auth\Access\Response as AccessResponse;
use Illuminate\Http\Response;

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
    }

    public function farms($account_id)
    {
        $user = auth()->user();
        $farms = $user->getAccount($account_id)->getUserFarms($user->id);

        return FarmResource::collection($farms);
    }

    public function farmsByUser()
    {
        $user = auth()->user();
        $accs = $user->accounts;

        $farms = [];
        foreach ($accs as $acc) {
            $uac = $user->getAccount($acc->id);
            $acFarms = $uac->getUserFarms($user->id);
            $role = $uac->pivot->roles[0]['name'];
            $access = $uac->pivot->user_access;
            foreach ($acFarms as $acFarm) {
                $lines = Farm::with(['lines', 'lines.harvests'])->find($acFarm->id)->lines->toArray();
                $alines = array_filter($lines, function ($line) use($role, $access) {
                    if ($role == 'admin' || $role == 'owner') {
                        return true;
                    }
                    return in_array($line['id'], json_decode($access)->line_id);
                });
                $farms[] = [
                    'id' => $acFarm->id,
                    'name' => $acFarm->name,
                    'number' => $acFarm->farm_number,
                    'acc_id' => $acc->id,
                    'lines' => array_map(function($line) {
                        $harvest_id = '';
                        foreach ($line['harvests'] as $harvest) {
                            if ($harvest['harvest_complete_date'] == 0)
                                $harvest_id = $harvest['id'];
                        }
                        $latestAss = '';
                        if ($harvest_id) {
                            $ass = HarvestGroup::find($harvest_id)->assessments;
                            if (count($ass)) {
                                $latestAss = $ass[0];
                                foreach ($ass as $el) {
                                    if ($latestAss['date_assessment'] <= $el['date_assessment']) {
                                        $latestAss = $el;
                                    }
                                }
                            } else {
                                $latestAss = [
                                    "id" => "",
                                    "harvest_group_id" => "",
                                    "color" => "",
                                    "condition_min" => "",
                                    "condition_max" => "",
                                    "condition_avg" => "",
                                    "blues" => "",
                                    "tones" => "",
                                    "planned_date_harvest" => "",
                                    "comment" => "",
                                    "created_at" => "",
                                    "updated_at" => "",
                                    "deleted_at" => "",
                                    "date_assessment" => "",
                                    "condition_score" => ""
                                ];
                            }
                        }
                        return [
                            'id' => $line['id'],
                            'line_name' => $line['line_name'],
                            'harvest_id' => $harvest_id,
                            'last_assess' => $latestAss,
                            'status' => $harvest_id ? 'seeded' : 'empty'
                        ];
                    }, $alines),
                ];
            }
        }
        return response()->json($farms);
    }


    public function lineSorting($request)
    {
        
        $userId = auth()->user()->id;
        $farmId = $request->input( 'farmId' );
        $columnKey = $request->input( 'columnKey' );
        $order = $request->input( 'orders' );

        $userSettingsTable = UserSettings::where('user_id','=', $userId)->get();

        if(count($userSettingsTable)){

            $userTables = $userSettingsTable[0];
            $json = $userTables->json;
            $decodeJson = json_decode($json);
            $returnJson = $this->manipulateJson($decodeJson,$farmId,$columnKey,$order);
        
            UserSettings::where('user_id', $userId)
                ->update(['json' => json_encode($returnJson)]);

        }else {
            $userSettings = new UserSettings;
            $userSettings->user_id = $userId;
            $userSettings->type = "line-sorting";
            $userSettings->json = json_encode([["farm_id"=>$farmId,"name"=>$columnKey,"order"=>$order]]);
            $userSettings->save();
        }

        return Response(["ack"=>1,"msg"=>"success"])->status( 200 );
    }

    public function manipulateJson($json,$farmId,$name,$order)
    {
         $arr = [];
          if(count($json) > 0){
              $available = false;
               foreach($json as $jsons){
                     if($jsons->farm_id == $farmId){
                         $available = true;
                         array_push($arr,["farm_id"=>$farmId,"name"=>$name,"order"=>$order]);
                     }else {
                        array_push($arr,$jsons);
                     }  
               }

               if(!$available){
                   array_push($arr,["farm_id"=>$farmId,"name"=>$name,"order"=>$order]);
               }
          }

          if(count($arr)){
               return $arr;
          }else {
               return $json;
          }
    }

    public function getLineSorting($request)
    {
        $userId = auth()->user()->id;
        $farmId = $request->input( 'farmId' );

        $userSettingsTable = UserSettings::where('user_id','=', $userId)->get();
        if(count($userSettingsTable)){

            $getUserSettings = $userSettingsTable[0];
            $json = $getUserSettings->json;
            $decodeJson = json_decode($json);

            $name = '';
            $order = '';
            
            foreach($decodeJson as $decodeJsons){
                if($farmId == $decodeJsons->farm_id){
                    $name = $decodeJsons->name;
                    $order = $decodeJsons->order;
                }
            }

            return response()->json( ['msg' => 'success', 'ack' => 1, 'data'=>['column_name'=>$name, 'column_order'=>$order]] );

        }else {
            return response()->json( ['msg' => 'data not found ', 'ack' => 0, 'data'=>['column_name'=>'', 'column_order'=>'']] );
        }
        
    }
}
