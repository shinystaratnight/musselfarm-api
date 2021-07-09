<?php

namespace App\Http\Controllers\Automation;

use App\Http\Requests\Automation\AutomationRequest;
use App\Models\Automation;
use App\Models\Inviting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class AutomationController extends Controller
{
    public function index()
    {
        $automations = [];
        if(auth()->user()->roles[0]['name'] === 'owner') {
            $users = Inviting::with('users')->where('inviting_user_id', auth()->user()->id)->get();

            $userIds = [ auth()->user()->id ];
            foreach($users as $user) {
                $userIds[] = $user->invited_user_id;
            }

            $automations = Automation::whereIn('creator_id', $userIds)->get();

        } else {
            $automations = Automation::where('creator_id', auth()->user()->id)->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $automations,
        ], 200);
    }

    public function store(AutomationRequest $request)
    {
        $attr = $request->validated();

        $task = Automation::create([
            'creator_id' => auth()->user()->id,
            'condition' => $attr['condition'],
            'action' => $attr['action'],
            'time' => $attr['time'],
            'title' => $attr['title'],
            'description' => $attr['description'],
        ]);

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
        $automation->description = $attr['description'];

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
