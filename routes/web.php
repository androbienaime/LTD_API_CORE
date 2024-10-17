<?php

use App\Http\Controllers\Core\InvoiceController;
use App\Models\Core\Order;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
$middleware = [
    'auth:web',
    'web'
];

Route::get('/', function () {
    return view('index');
});

Route::middleware($middleware)->group(function (){
    Route::get('invoice/{order}', [InvoiceController::class, 'show'])->name('invoice.show');
    
    // Route::get('orders/{model}/print', function (Order $model){
    //      return view('orders.print', compact('model'));
    // })->name('order.print');
 });
