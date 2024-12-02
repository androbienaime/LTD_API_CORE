<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\JWTMiddleware;
use App\Http\Controllers\Api\Core\AuthController;
use App\Http\Controllers\Api\Core\ShopController;
use App\Http\Controllers\Api\Core\OrderController;
use App\Http\Controllers\Api\Core\AccountController;
use App\Http\Controllers\Api\Core\CurrencyController;
use App\Http\Controllers\Api\Core\ProductController;
use App\Http\Controllers\Api\Core\CustomerController;
use App\Http\Controllers\Api\Location\CityController;
use App\Http\Controllers\Api\Location\StateController;
use App\Http\Controllers\Api\Location\CountryController;

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
Route::apiResource("orders", OrderController::class);
Route::apiResource("customers", CustomerController::class);
Route::apiResource("shops", ShopController::class);

Route::get("order-state/{order}", [OrderController::class, 'showState'])->name("order-show-state");
Route::post("order-state/{order}", [OrderController::class, 'changeState'])->name("order-change-state");

Route::post("orders/calculate-sub-total-live", [OrderController::class, "calculateSubTotalLive"]);
Route::post("orders/calculate-total-live", [OrderController::class, "calculateTotalLive"]);

Route::apiResource("currencies", CurrencyController::class)->only(["show", "index"]);
Route::get("currency/by-iso-code/{code}", [CurrencyController::class, 'getCurrencyByCode'])->name("currency-by-code.show");

Route::apiResource('countries', CountryController::class);
Route::apiResource('states', StateController::class);
Route::apiResource('cities', CityController::class);

// Filtres et recherches
Route::get('country/by-iso-code/{code}', [CountryController::class, 'getCountryByCode']);
Route::get('states/by-country/{country}', [StateController::class, 'getStatesByCountry']);
Route::get('cities/by-state/{state}', [CityController::class, 'getCitiesByState']);

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
