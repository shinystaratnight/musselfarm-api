<?php

namespace App\Http\Controllers;

use App\Models\FarmUtil;
use App\Http\Requests\Util\UtilRequest;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UtilController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $utils = FarmUtil::where('user_id', $user->id)->where('account_id', $request->input('account_id'))->get();

        return response()->json([
            'status' => 'success',
            'data' => $utils,
        ], 200);
    }

    public function store(UtilRequest $request)
    {
        $attr = $request->validated();

        $util = FarmUtil::create([
            'user_id' => auth()->user()->id,
            'account_id' => $attr['account_id'],
            'name' => $attr['name'],
            'type' => $attr['type'],
        ]);

        return response()->json(['status' => 'success'], 200);
    }
    
    public function update(UtilRequest $request, $id)
    {
        $attr = $request->validated();

        $util = FarmUtil::find($id);

        $util->name = $attr['name'];
        $util->type = $attr['type'];

        $util->save();
        return response()->json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {
        $util = FarmUtil::find($id);
        $util->delete();
        return response()->json(['status' => 'success'], 200);
    }

    public function allUtilsByUser(Request $request)
    {
        $user = auth()->user();
        $accs = $user->accounts;

        $utils = [];
        foreach ($accs as $acc) {
            $ac_utils = FarmUtil::where('account_id', $acc->id)->where('user_id', auth()->user()->id)->get()->toArray();
            $utils = array_merge($utils, array_map(function($util) {
                return [
                    'id' =>  $util['id'],
                    'name' =>  $util['name'],
                    'type' =>  $util['type'],
                    'account_id' =>  $util['account_id'],
                ];
            }, $ac_utils));
        }
        return response()->json($utils);
    }
}
