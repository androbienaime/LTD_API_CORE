<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JWTMiddleware;
use App\Http\Controllers\Api\Core\AuthController;
use App\Http\Controllers\Api\Core\AccountController;
use App\Http\Controllers\Api\Core\ProductController;

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

// Route::get("products/list", [ProductController::class, 'index']);
// Route::post("product/create", [ProductController::class, 'create']);

Route::apiResource("products", ProductController::class);

Route::prefix('account')->group(function () {
    Route::post('register', [AccountController::class, 'register']);
    Route::post('login', [AccountController::class, 'login']);
    Route::post('refresh', [AccountController::class, 'refresh']);
    
    Route::middleware('auth:account')->group(function () {
        Route::get('profile', [AccountController::class, 'profile']);
        Route::post('logout', [AccountController::class, 'logout']);
    });
});
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
