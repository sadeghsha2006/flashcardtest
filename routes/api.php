<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\AuthController;

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
Route::prefix('v1')->group(function(){
    Route::post('sign-in', [AuthController::class,'login']);
    Route::post('sign-up', [AuthController::class,'register']);
    Route::post('refresh-token',[AuthController::class,'refreshToken']);
   });
   Route::middleware(['auth:api'])->prefix('v1')->group(function () {
    Route::get('logout',[AuthController::class,'logout']);
    Route::get('user',[AuthController::class,'getUser']);
});


