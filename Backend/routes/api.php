<?php

use App\Http\Controllers\PositionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PersonController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('favouritesByUserId/{userId}', [FavouriteController::class, 'getByUserId']);

// Route::post('/favourites', [FavouriteController::class, 'store'])->withoutMiddleware('auth:api');
// users
Route::post('users/login', [UserController::class, 'login']);
Route::post('users/logout', [UserController::class, 'logout']);
Route::patch('users/change-password', [UserController::class, 'changePassword']);
    // ->middleware('auth:sanctum');
Route::get('users', [UserController::class, 'index'])
    ->middleware('auth:sanctum');

Route::get('users/{id}', [UserController::class, 'show'])
    ->middleware('auth:sanctum');
Route::post('users', [UserController::class, 'store']);

Route::patch('users/{id}', [UserController::class, 'update'])
    ->middleware('auth:sanctum');
Route::delete('users/{id}', [UserController::class, 'destroy']);

// positions
Route::get('positions', [PositionController::class, 'index']);
Route::get('positions/{id}', [PositionController::class, 'show']);
Route::post('positions', [PositionController::class, 'store'])
    ->middleware('auth:sanctum');
Route::patch('positions/{id}', [PositionController::class, 'update'])
    ->middleware('auth:sanctum');
Route::delete('positions/{id}', [PositionController::class, 'destroy'])
    ->middleware('auth:sanctum');
// films
Route::get('queryFilmsWithEvaluation', [FilmController::class, 'queryFilmsWithEvaluation']);
Route::get('films', [FilmController::class, 'index']);
Route::get('films/{id}', [FilmController::class, 'show']);
Route::post('films', [FilmController::class, 'store'])
    ->middleware('auth:sanctum');
Route::patch('films/{id}', [FilmController::class, 'update'])
    ->middleware('auth:sanctum');
Route::delete('films/{id}', [FilmController::class, 'destroy'])
    ->middleware('auth:sanctum');
// videos
Route::get('videos', [VideoController::class, 'index']);
Route::get('videos/{id}', [VideoController::class, 'show']);
Route::post('videos', [VideoController::class, 'store'])
    ->middleware('auth:sanctum');
Route::patch('videos/{id}', [VideoController::class, 'update'])
    ->middleware('auth:sanctum');
Route::delete('videos/{id}', [VideoController::class, 'destroy'])
    ->middleware('auth:sanctum');
// favourites
Route::get('favourites', [FavouriteController::class, 'index']);
Route::get('favourites/{id}', [FavouriteController::class, 'show']);
Route::get('favourites/{userId}/{filmId}', [FavouriteController::class, 'showFavouriteByUserIdAndFilmId']);
Route::post('favourites', [FavouriteController::class, 'store']);
Route::post('favourites/{userId}/{filmId}', [FavouriteController::class, 'storeFavouriteByUserIdAndFilmId']);
    // ->middleware('auth:sanctum');
Route::patch('favourites/{id}', [FavouriteController::class, 'update']);
Route::patch('favourites/{userId}/{filmId}', [FavouriteController::class, 'patchFavouriteByUserIdAndFilmId']);
// ->middleware('auth:sanctum');
Route::delete('favourites/{id}', [FavouriteController::class, 'destroy']);
Route::delete('favourites/{userId}/{filmId}', [FavouriteController::class, 'destroyFavouriteByUserIdAndFilmId']);
// ->middleware('auth:sanctum');
// roles
Route::get('roles', [RoleController::class, 'index']);
Route::get('roles/{id}', [RoleController::class, 'show']);
Route::post('roles', [RoleController::class, 'store'])
    ->middleware('auth:sanctum');
Route::patch('roles/{id}', [RoleController::class, 'update'])
    ->middleware('auth:sanctum');
Route::delete('roles/{id}', [RoleController::class, 'destroy'])
    ->middleware('auth:sanctum');
// people
Route::get('people', [PersonController::class, 'index']);
Route::get('people/{id}', [PersonController::class, 'show']);
Route::post('people', [PersonController::class, 'store'])
    ->middleware('auth:sanctum');
Route::patch('people/{id}', [PersonController::class, 'update'])
    ->middleware('auth:sanctum');
Route::delete('people/{id}', [PersonController::class, 'destroy'])
    ->middleware('auth:sanctum');
// tasks
Route::get('tasks', [TaskController::class, 'index']);
Route::get('tasks/{id}', [TaskController::class, 'show']);
Route::post('tasks', [TaskController::class, 'store'])
    ->middleware('auth:sanctum');
Route::patch('tasks/{id}', [TaskController::class, 'update'])
    ->middleware('auth:sanctum');
Route::delete('tasks/{id}', [TaskController::class, 'destroy'])
    ->middleware('auth:sanctum');

// 
Route::get('/', function () {
    return 'API';
});
