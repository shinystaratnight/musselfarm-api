<?php

namespace App\Http\Controllers\Task;

use App\Models\Task;
use App\Http\Requests\Task\TaskRequest;
use App\Http\Controllers\Controller;

class TaskController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $tasks = Task::where('owner_id', $user->id)->get();

        return response()->json([
            'status' => 'success',
            'data' => $tasks,
        ], 200);
    }

    public function store(TaskRequest $request)
    {
        $attr = $request->validated();

        $task = Task::create([
            'owner_id' => auth()->user()->id,
            'farm_id' => $attr['farm_id'],
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
