<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\StopController;
use Illuminate\Http\Request;
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

Route::get('account/sync', [AccountController::class, 'sync']);
Route::apiSingleton('account', AccountController::class)->only('show', 'update');
Route::apiResource('calendars', CalendarController::class)->scoped();
Route::apiResource('calendars.events', EventController::class)->scoped()->only('index');
Route::apiResource('logs', LogController::class)->scoped()->only('index');
Route::get('rides/ical', [RideController::class, 'ical']);
Route::apiResource('rides', RideController::class)->scoped()->only('index');
Route::get('stops/sync', [StopController::class, 'sync']);
