<?php

namespace App\Http\Controllers;

use App\Models\FarmUtil;
use App\Http\Requests\Util\UtilRequest;
use App\Http\Controllers\Controller;

class UtilController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $utils = FarmUtil::where('user_id', $user->id)->get();

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
}
