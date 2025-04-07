<?php

use App\Http\Controllers\DriverController;
use App\Http\Controllers\RideRequestController;
use App\Http\Controllers\RideController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\Driver\AuthController as DriverAuthController;
use App\Http\Controllers\Auth\User\AuthController as UserAuthController;

Route::group(["prefix"=> "users"], function () {
    Route::get('status', [UserController::class, 'status'])->middleware('auth:sanctum,user');

    Route::post('login', [UserAuthController::class,'login']);

    Route::post('logout', [UserAuthController::class,'logout'])->middleware('auth:sanctum,user');
});

Route::group(['prefix'=> 'drivers'], function () {
    Route::post('/location', [DriverController::class,'updateLocation'])->middleware('auth:sanctum,driver');

    Route::post('login', [DriverAuthController::class,'login']);

    Route::post('logout', [DriverAuthController::class,'logout'])->middleware('auth:sanctum,driver');
});

Route::post('ride-requests/store', [RideRequestController::class, 'store'])->middleware('auth:sanctum,user');
Route::get('ride-requests/cancel', [RideRequestController::class, 'cancel'])->middleware('auth:user');

Route::post('ride/complete', [RideController::class,'complete'])->middleware('auth:sanctum,driver');

