<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\CategoryController;

Route::get('/categories', [CategoryController::class, 'index']);
Route::post('/categories/add', [CategoryController::class, 'store']);
Route::delete('/categories/{id}', [CategoryController::class, 'delete']);

Route::get('/tasks', [TasksController::class, 'index']);
Route::post('/tasks/add', [TasksController::class, 'store']);
Route::get('/tasks/{id}', [TasksController::class, 'show']);
Route::put('/tasks/{id}', [TasksController::class, 'update']);
Route::patch('/tasks/{id}/complete', [TasksController::class, 'markAsCompleted']);
Route::delete('/tasks/{id}', [TasksController::class, 'delete']);
