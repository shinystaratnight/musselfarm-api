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
        $automations = Automation::where('account_id', $request->input('account_id'))->get();
        
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
            'account_id' => $attr['account_id'],
            'action' => $attr['action'],
            'time' => $attr['time'],
            'title' => $attr['title'],
            'unit' => $attr['unit'],
            'description' => $attr['description'],
        ];
        if ($attr['assigned_to'] > 0) {
            $aut['assigned_to'] = $attr['assigned_to'];
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
        if ($attr['assigned_to'] > 0) {
            $automation->assigned_to = $attr['assigned_to'];
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
