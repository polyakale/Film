<?php

use App\Http\Controllers\PositionController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// positions
Route::get('positions', [PositionController::class, 'index']);
Route::get('positions/{id}', [PositionController::class, 'show']);
Route::post('positions', [PositionController::class, 'store']);
Route::patch('positions/{id}', [PositionController::class, 'update']);
Route::delete('positions/{id}', [PositionController::class, 'destroy']);
// films
Route::get('films', [FilmController::class, 'index']);
Route::get('films/{id}', [FilmController::class, 'show']);
Route::post('films', [FilmController::class, 'store']);
Route::patch('films/{id}', [FilmController::class, 'update']);
Route::delete('films/{id}', [FilmController::class, 'destroy']);
// videos
Route::get('videos', [VideoController::class, 'index']);
Route::get('videos/{id}', [VideoController::class, 'show']);
Route::post('videos', [VideoController::class, 'store']);
Route::patch('videos/{id}', [VideoController::class, 'update']);
Route::delete('videos/{id}', [VideoController::class, 'destroy']);
// favourites
Route::get('favourites', [FavouriteController::class, 'index']);
Route::get('favourites/{id}', [FavouriteController::class, 'show']);
Route::post('favourites', [FavouriteController::class, 'store']);
Route::patch('favourites/{id}', [FavouriteController::class, 'update']);
Route::delete('favourites/{id}', [FavouriteController::class, 'destroy']);
// roles
Route::get('roles', [RoleController::class, 'index']);
Route::get('roles/{id}', [RoleController::class, 'show']);
Route::post('roles', [RoleController::class, 'store']);
Route::patch('roles/{id}', [RoleController::class, 'update']);
Route::delete('roles/{id}', [RoleController::class, 'destroy']);
// people
Route::get('people', [PersonController::class, 'index']);
Route::get('people/{id}', [PersonController::class, 'show']);
Route::post('people', [PersonController::class, 'store']);
Route::patch('people/{id}', [PersonController::class, 'update']);
Route::delete('people/{id}', [PersonController::class, 'destroy']);
// tasks
Route::get('tasks', [TaskController::class, 'index']);
Route::get('tasks/{id}', [TaskController::class, 'show']);
Route::post('tasks', [TaskController::class, 'store']);
Route::patch('tasks/{id}', [TaskController::class, 'update']);
Route::delete('tasks/{id}', [TaskController::class, 'destroy']);

// 
Route::get('/', function () {
    return 'API';
});
