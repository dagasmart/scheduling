<?php

use DagaSmart\Scheduling\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::get('scheduling', [Controllers\SchedulingController::class, 'index'])->name('scheduling-index');
Route::post('scheduling/run', [Controllers\SchedulingController::class, 'runEvent'])->name('scheduling-run');
