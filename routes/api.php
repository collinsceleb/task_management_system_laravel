<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'store'])->name('register')->middleware('guest');
Route::post('/login', [AuthController::class, 'show'])->name('login')->middleware('guest');
Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/create-task', [TaskController::class, 'store'])->name('create-task')->middleware('role:admin,manager');
    Route::post('/tasks/{taskId}/assign', [TaskController::class, 'assignTask'])->middleware('role:admin,manager');
    Route::get('/get-tasks', [TaskController::class, 'index'])->name('get-tasks');
    Route::put('/update-task/{id}', [TaskController::class, 'update'])->name('update-task');
    Route::delete('/delete-task/{id}', [TaskController::class, 'destroy'])->name('delete-task');
    Route::post('/complete-task/{id}', [TaskController::class, 'markTaskAsCompleted'])->name('complete-task');
});
