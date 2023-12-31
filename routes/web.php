<?php

use App\Livewire\Agenda;
use App\Models\Account;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', fn (Account $account) => redirect()->route('agenda', ['account' => $account]));
Route::get('/agenda', Agenda::class)->name('agenda');
