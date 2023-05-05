<?php


use App\Http\Controllers\CartItemController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AuthController;
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

// whole route: /api/...
// unprotected routes 
Route::get("/products", [ProductController::class, 'index']);
Route::post("/products", [ProductController::class, 'store']);
Route::get("/products/{id}", [ProductController::class, 'show']);
Route::put("/products/{id}", [ProductController::class, 'update']);
Route::delete("/products/{id}", [ProductController::class, 'destroy']);

Route::get("/user/all", [AuthController::class, 'index']);
Route::post("/user/register", [AuthController::class, 'register']);
Route::post("/user/login", [AuthController::class, 'login']);

// protected routes 
// to access these routes, 
Route::group([ 'middleware' => ['auth:sanctum']], function () {

    Route::get("/carts", [CartController::class, 'index']);
    Route::post("/carts", [CartController::class, 'store']);
    Route::get("/carts/{id}", [CartController::class, 'show']);
    Route::put("/carts/{id}", [CartController::class, 'update']);
    Route::delete("/carts/{id}", [CartController::class, 'destroy']);

    Route::get("/cart_items", [CartItemController::class, 'index']);
    Route::post("/cart_items", [CartItemController::class, 'store']);
    Route::get("/cart_items/{id}", [CartItemController::class, 'show']);
    Route::get("/cart_items/{cart_id}/{product_id}", [CartItemController::class, 'get_cart_item_with_cart_id_and_product_id']);
    Route::put("/cart_items/{id}", [CartItemController::class, 'update']);
    Route::delete("/cart_items/{id}", [CartItemController::class, 'destroy']);

    Route::post("/user/logout", [AuthController::class, 'logout'])->middleware('auth:sanctum');

});
