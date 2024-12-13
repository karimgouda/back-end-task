<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:api');

Route::middleware('auth:api')->controller(PostController::class)
    ->group(function () {
    Route::get('/posts','posts');
    Route::get('/show-post/{id}','showPost');
    Route::post('/create','create');
    Route::put('/update/{id}','update');
    Route::delete('/delete/{id}','delete');
    Route::get('/posts/search', 'search');
    Route::get('/posts/filter', 'filterPosts');
});

Route::middleware('auth:api')->controller(CommentController::class)->group(function (){
    Route::post('/posts/{id}/comments','comment');
});

