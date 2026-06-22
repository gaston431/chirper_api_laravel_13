<?php

use App\Http\Controllers\CalendarController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/redirectCalendar', [CalendarController::class, 'redirectCalendar'])->name('redirectUri');
Route::get('/createEvent', [CalendarController::class, 'createEvent'])->name('createEvent');

Route::get('/sessionDestroy', function () {
    session_start();
    session_destroy(); // <-- AGREGA ESTO TEMPORALMENTE
    $_SESSION = [];    // Limpia la memoria actual
});
