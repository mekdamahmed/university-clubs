<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ApiResponse;

    public function clubTasks($club_id)
    {
        // الليدر يرى كل التاسكات وتاريخها وحالتها
        $tasks = Task::with('assignee')->where('club_id', $club_id)->orderBy('created_at', 'desc')->get();
        return $this->successResponse($tasks);
    }

    public function store(Request $request)
    {
        $request->validate([
            'club_id' => 'required|exists:clubs,id', 
            'assigned_to' => 'required|exists:users,id',
            'title' => 'required', 
            'description' => 'required',
            'due_date' => 'required|date' // New deadline validation
        ]);
        Task::create($request->all());
        return $this->successResponse(null, 'Task Assigned successfully');
    }

    public function myTasks(Request $request)
    {
        $tasks = \App\Models\Task::with('club')->where('assigned_to', $request->user()->id)->orderBy('due_date')->get();
        return $this->successResponse($tasks);
    }

    public function complete(Request $request, $id)
    {
        $task = Task::findOrFail($id);
        if (\Carbon\Carbon::parse($task->due_date)->isPast() && !$task->is_completed) {
            return $this->errorResponse('Cannot complete a failed task!', 400);
        }
        $task->update(['is_completed' => true]);
        return $this->successResponse(null, 'Task marked as done');
    }
}