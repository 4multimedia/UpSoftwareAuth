<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\StartSession;

Route::get('user', UserController::class)->name('user')->middleware('auth:sanctum');

Route::prefix('login')->middleware([StartSession::class])->group(function () {
    Route::post('/', 'LoginController@login')->name('login');
    Route::post('/validate', 'LoginController@validate')->name('login.validate');
});

Route::prefix('register')->middleware([StartSession::class])->group(function () {
    Route::post('/', 'RegisterController@register')->name('register');
});

 Route::post('/check-email', CheckEmailController::class)->name('check-email');

 Route::prefix('otp')->group(function () {
     Route::post('/expiry-time', 'OtpController@expiryTime')->name('expiry-time');
     Route::post('/renew', 'OtpController@renew')->name('renew');
 });
