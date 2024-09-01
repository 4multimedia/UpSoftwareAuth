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

