<?php

use Illuminate\Support\Facades\Route;

Route::post('/login', LoginController::class)->name('login');
Route::post('/otp', OtpController::class)->name('login');
