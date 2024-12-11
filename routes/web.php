<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Controllers\HeatmapController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\OccurrenceController;


Route::get('/', [App\Http\Controllers\HeatmapController::class, 'index'])->name('index');
Route::get('/home', [App\Http\Controllers\HeatmapController::class, 'index'])->name('home');

Route::get('/heatmap/data/{productid?}/{days?}/{status?}', [HeatmapController::class, 'getData'])->name('heatmap.data');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/occurrence/store', [OccurrenceController::class, 'store'])->name('occurrence.store');
    Route::get('/occurrence/create', [OccurrenceController::class, 'create'])->name('occurrence.create');

});

Auth::routes();


//Route::get('/occurrence', [OccurrenceController::class, 'index'])->name('occurrence.index');






Route::middleware([AdminMiddleware::class])->group(function () {
    Route::get('/occurrence', [OccurrenceController::class, 'index'])->name('occurrence.index');
});

Route::patch('/occurrence/{id}/toggle', [OccurrenceController::class, 'toggleResolution'])->name('occurrence.toggle');


//Route::get('/occurrence/edit', [OccurrenceController::class, 'edit'])->name('occurrence.edit');
//Route::post('/occurrence/destroy', [OccurrenceController::class, 'destroy'])->name('occurrence.destroy');



