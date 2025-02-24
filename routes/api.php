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
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/tasks/create', [TaskController::class, 'store'])->name('create')->middleware('role:admin,manager');
    Route::post('/tasks/{taskId}/assign', [TaskController::class, 'assignTask'])->middleware('role:admin,manager');
    Route::get('/tasks/get-tasks', [TaskController::class, 'index'])->name('get-tasks');
    Route::put('/tasks/{id}/update', [TaskController::class, 'update'])->name('update');
    Route::delete('/tasks/{id}/delete', [TaskController::class, 'destroy'])->name('delete-task');
    Route::post('/tasks/{taskId}/complete', [TaskController::class, 'markTaskAsCompleted'])->name('complete-task');
    Route::get('/tasks/assigned/{userId}', [TaskController::class, 'show'])->name('assigned-tasks');

});
