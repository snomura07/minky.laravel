<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', DashboardController::class);
Route::get('/dashboard', DashboardController::class)->name('dashboard');
