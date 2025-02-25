<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    const TASKS_RETRIEVED_SUCCESSFULLY = 'Tasks retrieved successfully';

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $tasks = Task::where('user_id', $user->id)->get();
            Log::info('User: ' . $user);
            Log::info('Tasks: ' . $tasks);
            if (!$user->id) {
                $response = ['message' => 'Unauthorized'];
                $status = 401;
            } elseif ($user->isAdmin() || $user->isManager()) {
                $response = $tasks;
                $status = 200;
            } else {
                $response = ['error' => 'Unauthorized to view tasks'];
                $status = 403;
            }
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Failed to retrieve tasks: ' . $e->getMessage(),
            ];
            $status = 500;
        }
        return response()->json($response, $status);
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
                $response = ['message' => 'Unauthorized'];
                $status = 401;
            } elseif (!$user->role == 'admin' || !$user->role == 'manager') {
                $response = ['error' => 'Unauthorized to create task'];
                $status = 403;
            } else {
                $taskData = [
                    'user_id' => $user->id,
                    'title' => $request->title,
                    'description' => $request->description,
                    'priority' => $request->priority,
                    'status' => $request->status,
                    'due_date' => $request->due_date,
                ];

                Log::info('Task Data Before Insert:', $taskData);

                $task = Task::create($taskData);

                $response = [
                    'success' => true,
                    'message' => 'Task created successfully',
                    'data' => $task,
                ];
                $status = 201;
            }
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Failed to create task: ' . $e->getMessage(),
            ];
            $status = 500;
        }

        return response()->json($response, $status);
    }
    /**
     * Display the specified resource.
     */
    public function show(Request $request, $userId)
    {
        try {
            $user = $request->user();
            $tasks = Task::where('assigned_to', $userId)->get();

            if (!$user->role == 'admin' && !$user->role == 'manager') {
                $response = ['error' => 'Unauthorized to view tasks'];
                $status = 401;
            } elseif (!$tasks) {
                $response = ['message' => 'Task not found'];
                $status = 404;
            } elseif ($user->role == 'user' && $user->id == $userId) {
                $response = $tasks;
                $status = 200;
            }
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Failed to retrieve tasks: ' . $e->getMessage(),
            ];
            $status = 500;
        }
        return response()->json($response, $status);
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
                $response = ['message' => 'Unauthorized'];
                $status = 401;
            } else {
                $task = Task::where('user_id', $user->id)->where('id', $id)->first();

                if (!$task) {
                    $response = ['message' => 'Task not found'];
                    $status = 404;
                } else {
                    $task->update($request->all());
                    $response = [
                        'success' => true,
                        'message' => 'Task updated successfully',
                        'data' => $task,
                    ];
                    $status = 200;
                }
            }
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Failed to update task: ' . $e->getMessage(),
            ];
            $status = 500;
        }

        return response()->json($response, $status);
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
                $response = ['message' => 'Unauthorized'];
                $status = 401;
            } elseif (!$task) {
                $response = ['message' => 'Task not found'];
                $status = 404;
            } else {
                $task->delete();
                $response = [
                    'success' => true,
                    'message' => 'Task deleted successfully',
                ];
                $status = 200;
            }
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Failed to delete task: ' . $e->getMessage(),
            ];
            $status = 500;
        }

        return response()->json($response, $status);
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


    public function assignTask(Request $request, $taskId)
    {
        try {
            $user = $request->user();
            $task = Task::findOrFail($taskId)->where('user_id', $user->id)->where('id', $taskId)->first();
            log::info('Task: ' . $task);

            $request->validate([
                'assigned_to' => 'required|exists:users,id',
            ]);

            if (!$task) {
                $response = ['message' => 'Task not found'];
                $status = 404;
            } elseif (!$user->isAdmin() && !$user->isManager()) {
                $response = ['error' => 'Unauthorized to assign tasks'];
                $status = 403;
            } else {
                $task->assigned_to = $request->assigned_to;
                $task->assignee = $user->id;
                $task->save();
                $response = [
                    'success' => true,
                    'message' => 'Task assigned to user successfully',
                    'data' => $task,
                ];
                $status = 200;
            }
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Failed to assign task to user: ' . $e->getMessage(),
            ];
            $status = 500;
        }

        return response()->json($response, $status);
    }

    public function getTaskById(Request $request, $taskId)
    {
        try {
            $user = $request->user();
            $task = Task::where('user_id', $user->id)->where('id', $taskId)->first();

            if (!$user->id) {
                $response = ['message' => 'Unauthorized'];
                $status = 401;
            } elseif (!$task) {
                $response = ['message' => 'Task not found'];
                $status = 404;
            } else {
                $response = $task;
                $status = 200;
            }
        } catch (\Exception $e) {
            $response = [
                'success' => false,
                'message' => 'Failed to retrieve task: ' . $e->getMessage(),
            ];
            $status = 500;
        }

        return response()->json($response, $status);
    }
}
