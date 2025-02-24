<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF Cookie Set']);
});
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/auth/register', [AuthController::class, 'store'])->name('register')->middleware('guest');
Route::post('/auth/login', [AuthController::class, 'show'])->name('login')->middleware('guest');
define('ROLE_ADMIN_MANAGER', 'role:admin,manager');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/tasks/create', [TaskController::class, 'store'])->name('create')->middleware(ROLE_ADMIN_MANAGER);
    Route::post('/tasks/{taskId}/assign', [TaskController::class, 'assignTask'])->middleware(ROLE_ADMIN_MANAGER);
    Route::get('/tasks/get-tasks', [TaskController::class, 'index'])->name('get-tasks')->middleware(ROLE_ADMIN_MANAGER);
    Route::put('/tasks/{id}/update', [TaskController::class, 'update'])->name('update');
    Route::delete('/tasks/{id}/delete', [TaskController::class, 'destroy'])->name('delete-task');
    Route::post('/tasks/{taskId}/complete', [TaskController::class, 'markTaskAsCompleted'])->name('complete-task');
    Route::get('/tasks/assigned/{userId}', [TaskController::class, 'show'])->name('assigned-tasks');
    Route::get('/tasks/get-task/{taskId}', [TaskController::class, 'getTaskById'])->name('get-task');
});
