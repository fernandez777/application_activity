<?php

use App\Http\Controllers\API\AuthController;
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

Route::middleware('auth:api')->group(function () {

    Route::get('/users', function(Request $request){
        return $request->user();
    });
});


/** Public Routes */
Route::post('/users/login', [AuthController::class, 'login']);
Route::post('/users', [AuthController::class, 'register']);
Route::post('/users/validate/otp', [AuthController::class, 'validateOtp']);

/** Private Routes */
Route::group(['middleware' => 'auth:sanctum'], function (){
    
    Route::post('/users/logout', [AuthController::class, 'logout']);
    Route::patch('/users/{user}', [AuthController::class, 'edit']);
    Route::delete('/users/{user}', [AuthController::class, 'destroy']);
});

