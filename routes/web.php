<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::redirect('/', 'api/documentation');




Route::get('/test-pusher', function () {
    return view('reverb');
});

Route::get('/test-pusher2', function () {
    return view('reverb2');
});
