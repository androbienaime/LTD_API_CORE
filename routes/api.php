<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Core\{
    ShopController,
    OrderController,
    AccountController,
    CategoryController,
    CurrencyController,
    ProductController,
    CustomerController,
    ShopMemberController,
};
use App\Http\Controllers\Api\Location\{
    CityController,
    StateController,
    CountryController,
};

/*
|--------------------------------------------------------------------------
| Routes publiques — aucune auth requise
|--------------------------------------------------------------------------
*/

// Devises
Route::prefix('currencies')->group(function () {
    Route::get('/',              [CurrencyController::class, 'index']);
    Route::get('/{currency}',   [CurrencyController::class, 'show']);
    Route::get('/by-iso-code/{code}', [CurrencyController::class, 'getCurrencyByCode'])
        ->name('currency-by-code.show');
});

// Localisation
Route::prefix('countries')->group(function () {
    Route::get('/',                      [CountryController::class, 'index']);
    Route::get('/{country}',             [CountryController::class, 'show']);
    Route::get('/by-iso-code/{code}',    [CountryController::class, 'getCountryByCode'])
        ->name('country-by-code.show');
});

Route::prefix('states')->group(function () {
    Route::get('/',                      [StateController::class, 'index']);
    Route::get('/{state}',               [StateController::class, 'show']);
    Route::get('/by-country/{country}',  [StateController::class, 'getStatesByCountry'])
        ->name('states-by-country.index');
});

Route::prefix('cities')->group(function () {
    Route::get('/',                  [CityController::class, 'index']);
    Route::get('/{city}',            [CityController::class, 'show']);
    Route::get('/by-state/{state}',  [CityController::class, 'getCitiesByState'])
        ->name('cities-by-state.index');
});

/*
|--------------------------------------------------------------------------
| Compte — auth account (JWT)
|--------------------------------------------------------------------------
*/
Route::prefix('account')->group(function () {
    Route::post('register', [AccountController::class, 'register']);
    Route::post('login', [AccountController::class, 'login']);
    Route::post('refresh', [AccountController::class, 'refresh']);
    
    Route::middleware('auth.account')->group(function () {
        Route::get('profile', [AccountController::class, 'profile']);
        Route::post('logout', [AccountController::class, 'logout']);
    });
});



//   Route::get('/shops/{shops}/products', [ShopController::class, 'getProductByShop'])
//                 ->name('shops.products.get-by-shop');

Route::post('/account/refresh', [AccountController::class, 'refresh']);

/*
|--------------------------------------------------------------------------
| Routes protégées — auth:account obligatoire
|--------------------------------------------------------------------------
*/
Route::middleware('auth.account')->group(function () {


    // ── Shops ─────────────────────────────────────────────────────────────
    // Lister et créer : pas de shop context encore (création = pas de pivot)
    Route::get('shops',      [ShopController::class, 'index'])->name('shops.index');
    Route::post('shops',     [ShopController::class, 'store'])->name('shops.store');
   

    Route::prefix('shops/{shop}')->group(function () {
        Route::get('/',    [ShopController::class, 'show'])->name('shops.show')
            ->middleware('shop.permission:shop.view');

        Route::put('/',    [ShopController::class, 'update'])->name('shops.update')
            ->middleware('shop.permission:shop.update');

        Route::patch('/',  [ShopController::class, 'update'])
            ->middleware('shop.permission:shop.update');

        Route::delete('/', [ShopController::class, 'destroy'])->name('shops.destroy')
            ->middleware('shop.permission:shop.delete');

        // ── Membres du shop ───────────────────────────────────────────────
        Route::prefix('members')->group(function () {

            Route::get('/', [ShopMemberController::class, 'index'])
                ->name('shop.members.index')
                ->middleware('shop.permission:shop.members.view');

            Route::post('/', [ShopMemberController::class, 'store'])
                ->name('shop.members.store')
                ->middleware('shop.permission:shop.members.attach');

            Route::put('/{account}', [ShopMemberController::class, 'update'])
                ->name('shop.members.update')
                ->middleware('shop.permission:shop.members.update_role');

            Route::delete('/{account}', [ShopMemberController::class, 'destroy'])
                ->name('shop.members.destroy')
                ->middleware('shop.permission:shop.members.detach');
        });

        // ── Produits ──────────────────────────────────────────────────────
        Route::prefix('products')->group(function () {

            Route::get('/',            [ProductController::class, 'index'])
                ->name('shop.products.index')
                ->middleware('shop.permission:product.view');

            Route::post('/',           [ProductController::class, 'store'])
                ->name('shop.products.store')
                ->middleware('shop.permission:product.create');

            Route::get('/{product}',   [ProductController::class, 'show'])
                ->name('shop.products.show');
                // ->middleware('shop.permission:product.view');

            Route::put('/{product}',   [ProductController::class, 'update'])
                ->name('shop.products.update')
                ->middleware('shop.permission:product.update');

            Route::patch('/{product}', [ProductController::class, 'update'])
                ->middleware('shop.permission:product.update');

            Route::delete('/{product}', [ProductController::class, 'destroy'])
                ->name('shop.products.destroy')
                ->middleware('shop.permission:product.delete');
        });

        // ── Catégories ────────────────────────────────────────────────────
        Route::prefix('categories')->group(function () {

            Route::get('/',              [CategoryController::class, 'index'])
                ->name('shop.categories.index')
                ->middleware('shop.permission:product.view');

            Route::post('/',             [CategoryController::class, 'store'])
                ->name('shop.categories.store')
                ->middleware('shop.permission:product.create');

            Route::get('/{category}',    [CategoryController::class, 'show'])
                ->name('shop.categories.show')
                ->middleware('shop.permission:product.view');

            Route::put('/{category}',    [CategoryController::class, 'update'])
                ->name('shop.categories.update')
                ->middleware('shop.permission:product.update');

            Route::delete('/{category}', [CategoryController::class, 'destroy'])
                ->name('shop.categories.destroy')
                ->middleware('shop.permission:product.delete');
        });

        // ── Commandes ─────────────────────────────────────────────────────
        Route::prefix('orders')->group(function () {

            Route::get('/',    [OrderController::class, 'index'])
                ->name('shop.orders.index');
                // ->middleware('shop.permission:order.view');

            Route::post('/',   [OrderController::class, 'store'])
                ->name('shop.orders.store');
                // ->middleware('shop.permission:order.create');

            Route::get('/{order}',    [OrderController::class, 'show'])
                ->name('shop.orders.show')
                ->middleware('shop.permission:order.view');

            Route::put('/{order}',    [OrderController::class, 'update'])
                ->name('shop.orders.update')
                ->middleware('shop.permission:order.update');

            Route::delete('/{order}', [OrderController::class, 'destroy'])
                ->name('shop.orders.destroy')
                ->middleware('shop.permission:order.delete');

            // Statut commande
            Route::get('/{order}/state',  [OrderController::class, 'showState'])
                ->name('shop.order-state.show')
                ->middleware('shop.permission:order.view');

            Route::post('/{order}/state', [OrderController::class, 'changeState'])
                ->name('shop.order-state.change')
                ->middleware('shop.permission:order.update');

            // Calculs live (pas de permission stricte, juste auth)
            Route::post('/calculate-sub-total', [OrderController::class, 'calculateSubTotalLive'])
                ->name('shop.orders.calculate-sub-total');

            Route::post('/calculate-total', [OrderController::class, 'calculateTotalLive'])
                ->name('shop.orders.calculate-total');
        });

        // ── Clients ───────────────────────────────────────────────────────
        Route::prefix('customers')->group(function () {

            Route::get('/',               [CustomerController::class, 'index'])
                ->name('shop.customers.index')
                ->middleware('shop.permission:customer.view');

            Route::post('/',              [CustomerController::class, 'store'])
                ->name('shop.customers.store');
                // ->middleware('shop.permission:customer.create');

            Route::get('/{customer}',     [CustomerController::class, 'show'])
                ->name('shop.customers.show')
                ->middleware('shop.permission:customer.view');

            Route::put('/{customer}',     [CustomerController::class, 'update'])
                ->name('shop.customers.update');
                // ->middleware('shop.permission:customer.update');

            Route::delete('/{customer}',  [CustomerController::class, 'destroy'])
                ->name('shop.customers.destroy')
                ->middleware('shop.permission:customer.delete');
        });
    });
});