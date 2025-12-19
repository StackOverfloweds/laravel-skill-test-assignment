<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Post\PostController;

Route::get('posts', [PostController::class, 'index']);
Route::get('posts/create', [PostController::class, 'create']);
Route::middleware('auth')->post('posts', [PostController::class, 'store']);
Route::get('posts/{id}', [PostController::class, 'show']);
Route::get('posts/{id}/edit', [PostController::class, 'edit']);
Route::middleware('auth')->put('posts/{id}', [PostController::class, 'update']);
Route::middleware('auth')->patch('posts/{id}', [PostController::class, 'update']);
Route::middleware('auth')->delete('posts/{id}', [PostController::class, 'destroy']);