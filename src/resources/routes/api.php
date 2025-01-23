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

Route::prefix('reset')->middleware([StartSession::class])->group(function () {
    Route::post('/', 'ResetController@reset')->name('reset');
    Route::post('/validate', 'ResetController@validate')->name('reset.validate');
    Route::post('/store', 'ResetController@store')->name('reset.store');
});

Route::post('/check-email', CheckEmailController::class)->name('check-email');

Route::prefix('otp')->group(function () {
     Route::post('/expiry-time', 'OtpController@expiryTime')->name('expiry-time');
     Route::post('/renew', 'OtpController@renew')->name('renew');
 });
