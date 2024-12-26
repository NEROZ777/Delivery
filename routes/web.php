<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/user/register', function() {
    return view('User.register');
});

Route::get('/user/login', function() {
    return view('User.login');
});
