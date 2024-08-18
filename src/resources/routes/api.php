<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Session\Middleware\StartSession;

Route::prefix('login')->middleware([StartSession::class])->group(function () {
    Route::post('/', 'LoginController@login')->name('login');
    Route::post('/validate', 'LoginController@validate')->name('login.validate');
});
