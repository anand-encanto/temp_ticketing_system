<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\TicketController;


Route::get('/', function () {
    return view('welcome');
});

// routes/web.php
Route::get('/tickets/{ticket}', [TicketController::class, 'show'])
    ->name('tickets.view')
    ->middleware('auth'); 

