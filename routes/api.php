<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;
use Illuminate\Http\Request;

Route::post('/login', [ApiController::class, 'login']);
Route::post('/register', [ApiController::class, 'register']);
Route::post('/insert', [ApiController::class, 'insert']);

Route::get('/products', [ApiController::class, 'getProducts']);

Route::get('/stats', [ApiController::class, 'getStats']);

//stats
