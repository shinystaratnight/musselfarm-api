<?php

namespace App\Http\Controllers\Task;

use App\Models\Task;
use App\Models\Inviting;
use App\Models\User;
use App\Http\Requests\Task\TaskRequest;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    public function index()
    {
        $tasks = [];
        if(auth()->user()->roles[0]['name'] === 'owner') {
            $users = Inviting::with('users')->where('inviting_user_id', auth()->user()->id)->get();

            $userIds = [ auth()->user()->id ];
            foreach($users as $user) {
                $userIds[] = $user->invited_user_id;
            }

            $tasks = Task::whereIn('creator_id', $userIds)->get();

        } else if (auth()->user()->roles[0]['name'] === 'admin') {
            
            $owner = Inviting::where('invited_user_id', auth()->user()->id)->first();

            $o = User::where('id', $owner['inviting_user_id'])->first();
            $users = Inviting::with('users')->where('inviting_user_id', $o['id'])->get();

            $userIds = [ $o['id'] ];
            foreach($users as $user) {
                $userIds[] = $user->invited_user_id;
            }

            $tasks = Task::whereIn('creator_id', $userIds)->get();

        } else {

            $tasks = Task::where('creator_id', auth()->user()->id)
                            ->orWhere('charger_id', auth()->user()->id)->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $tasks,
        ], 200);
    }

    public function store(TaskRequest $request)
    {
        $attr = $request->validated();

        $task = Task::create([
            'creator_id' => auth()->user()->id,
            'farm_id' => $attr['farm_id'],
            'title' => $attr['title'],
            'content' => $attr['content'],
            'charger_id' => $attr['charger_id'],
            'line_id' => $attr['line_id'],
            'due_date' => $attr['due_date'],
        ]);

        return response()->json(['status' => 'success'], 200);
    }
    
    public function update(TaskRequest $request, $id)
    {
        $attr = $request->validated();

        $task = Task::find($id);

        $task->farm_id = $attr['farm_id'];
        $task->line_id = $attr['line_id'];
        $task->charger_id = $attr['charger_id'];
        $task->title = $attr['title'];
        $task->content = $attr['content'];
        $task->due_date = $attr['due_date'];
        $task->active = $attr['active'];

        $task->save();
        return response()->json(['status' => 'success'], 200);
    }

    public function destroy($id)
    {
        $task = Task::find($id);
        $task->delete();
        return response()->json(['status' => 'success'], 200);
    }

    public function removeCompletedTasks()
    {
        Task::where('active', 1)->delete();
        return response()->json(['status' => 'success'], 200);
    }
}
