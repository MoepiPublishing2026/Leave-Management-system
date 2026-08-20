<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

Route::get('/health', [AuthController::class, 'check']);


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
| All authentication-related endpoints are defined here.
| This section includes login and registration routes.
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);



Route::middleware('auth:api')->get('/user', function (Request $request) {
    return response()->json([
        'status' => 1,
        'data' => [
            'email' => $request->user()->email,
        ],
    ]);
});