<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MessageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// System
Route::middleware(['role-system'])->group(function () {
    Route::put('/messages/{id}', [MessageController::class, 'update']);
});

// User
Route::middleware(['role-everyone'])->group(function () {
    Route::get('/messages', [MessageController::class, 'read']);
    Route::get('/messages/{id}', [MessageController::class, 'readById']);
    Route::post('/messages', [MessageController::class, 'create']);
});

Route::post('/registrar-cus-add', [CustomerController::class, 'register']);