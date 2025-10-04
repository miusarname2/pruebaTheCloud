<?php

use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\TaskController;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('test', function() {
    return response()->json(['message' => 'API is working']);
});
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::resource('tasks', TaskController::class)->middleware('auth:sanctum');
Route::patch('tasks/{task}/toggle', [TaskController::class, 'toggle'])->middleware('auth:sanctum');
