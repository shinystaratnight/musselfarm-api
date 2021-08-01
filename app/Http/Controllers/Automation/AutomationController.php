<?php

namespace App\Http\Controllers\Automation;

use App\Http\Requests\Automation\AutomationRequest;
use App\Models\Automation;
use App\Models\Inviting;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class AutomationController extends Controller
{
    public function index(Request $request)
    {
        $automations = [];
        $inviterId = $request->exists('inviter') ? $request->input('inviter') : 0;

        $userIds = auth()->user()->getProfileUserIds($inviterId);

        $automations = Automation::whereIn('creator_id', $userIds)->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $automations,
        ], 200);
    }

    public function store(AutomationRequest $request)
    {
        $attr = $request->validated();

        $aut = [
            'creator_id' => auth()->user()->id,
            'condition' => $attr['condition'],
            'action' => $attr['action'],
            'time' => $attr['time'],
            'title' => $attr['title'],
            'unit' => $attr['unit'],
            'description' => $attr['description'],
        ];
        if ($attr['charger_id'] > 0) {
            $aut['charger_id'] = $attr['charger_id'];
        }

        $task = Automation::create($aut);

        return response()->json(['status' => 'success'], 200);
    }

    public function show(LineBudget $lineBudget)
    {
        //
    }

    public function update(AutomationRequest $request, $id)
    {
        $attr = $request->validated();

        $automation = Automation::find($id);

        $automation->condition = $attr['condition'];
        $automation->action = $attr['action'];
        $automation->time = $attr['time'];
        $automation->title = $attr['title'];
        $automation->unit = $attr['unit'];
        $automation->description = $attr['description'];
        if ($attr['charger_id'] > 0) {
            $automation->charger_id = $attr['charger_id'];
        }

        $automation->save();
        return response()->json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {
        $automation = Automation::find($id);
        $automation->delete();
        return response()->json(['status' => 'success'], 200);
    }
}
