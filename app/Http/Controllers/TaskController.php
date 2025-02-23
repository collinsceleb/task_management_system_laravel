<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $tasks = Task::where('user_id', $user->id)->get();
        return response()->json([
            'success' => true,
            'message' => 'Tasks retrieved successfully',
            'data' => $tasks,
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        try {
            $user = $request->user();
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:pending,in_progress,completed',
                'priority' => 'required|in:low,medium,high',
                'due_date' => 'nullable|date',
                'assigned_to' => 'nullable|exists:users,id',
                'assignee' => 'nullable|string',
            ]);
            Log::info('User: ' . $user);

            if (!$user->id) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $taskData = [
                'user_id' => $user->id,
                'title' => $request->title,
                'description' => $request->description,
                'priority' => $request->priority,
                'status' => $request->status,
                'due_date' => $request->due_date,
                'assignee' => $user->id,
            ];

            Log::info('Task Data Before Insert:', $taskData);


            $task = Task::create($taskData);

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'data' => $task,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create task: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $user = $request->user();
            $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'required|in:pending,in_progress,completed',
                'priority' => 'required|in:low,medium,high',
                'due_date' => 'nullable|date',
                'assigned_to' => 'nullable|exists:users,id',
                'assignee' => 'nullable|string',
            ]);

            if (!$user->id) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $task = Task::where('user_id', $user->id)->where('id', $id)->first();

            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }

            $task->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Task updated successfully',
                'data' => $task,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        try {
            $user = $request->user();
            $task = Task::where('user_id', $user->id)->where('id', $id)->first();

            if (!$user->id) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }

            $task->delete();

            return response()->json([
                'success' => true,
                'message' => 'Task deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete task: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getTasksByStatus(Request $request, $status)
    {
        $user = $request->user();
        $tasks = Task::where('user_id', $user->id)->where('status', $status)->get();

        return response()->json([
            'success' => true,
            'message' => 'Tasks retrieved successfully',
            'data' => $tasks,
        ], 200);
    }

    public function getTasksByPriority(Request $request, $priority)
    {
        $user = $request->user();
        $tasks = Task::where('user_id', $user->id)->where('priority', $priority)->get();

        return response()->json([
            'success' => true,
            'message' => 'Tasks retrieved successfully',
            'data' => $tasks,
        ], 200);
    }

    public function markTaskAsCompleted(Request $request, $id)
    {

        try {
            $user = $request->user();
            $task = Task::where('user_id', $user->id)->where('id', $id)->first();

            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }

            $task->status = 'completed';
            $task->is_completed = true;
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task marked as completed successfully',
                'data' => $task,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark task as completed: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function changeTaskStatus(Request $request, $id, $status)
    {
        try {
            $user = $request->user();
            $task = Task::where('user_id', $user->id)->where('id', $id)->first();

            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }

            $task->status = $status;
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully',
                'data' => $task,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task status: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function changeTaskPriority(Request $request, $id, $priority)
    {
        try {
            $user = $request->user();
            $task = Task::where('user_id', $user->id)->where('id', $id)->first();

            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }

            $task->priority = $priority;
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task priority updated successfully',
                'data' => $task,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update task priority: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function assignTaskToUser(Request $request, $id, $userId)
    {
        try {
            $user = $request->user();
            $task = Task::where('user_id', $user->id)->where('id', $id)->first();

            if (!$task) {
                return response()->json(['message' => 'Task not found'], 404);
            }

            $task->assigned_to = $userId;
            $task->save();

            return response()->json([
                'success' => true,
                'message' => 'Task assigned to user successfully',
                'data' => $task,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign task to user: ' . $e->getMessage(),
            ], 500);
        }
    }
}
