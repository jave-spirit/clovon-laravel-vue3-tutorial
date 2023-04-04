<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/users', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/users/search', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/appointments', function (Request $request) {
    return $request->appointment();
});

Route::middleware('auth:sanctum')->get('/appointment-status', function (Request $request) {
    return $request->appointmentstatus();
});

Route::middleware('auth:sanctum')->get('/clients', function (Request $request) {
    return $request->getClients();
});


