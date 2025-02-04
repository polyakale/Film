<?php

use App\Http\Controllers\FilmController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// films
Route::get('films', [FilmController::class, 'index']);
Route::get('films/{id}', [FilmController::class, 'show']);
Route::post('films', [FilmController::class, 'store']);
Route::patch('films/{id}', [FilmController::class, 'update']);
Route::delete('films/{id}', [FilmController::class, 'destroy']);

// 
Route::get('/', function(){
    return 'API';
});
