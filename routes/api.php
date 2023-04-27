<?php


use App\Models\Product;
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
Route::get("/products", [ProductController::class, 'index']);
Route::post("/products", [ProductController::class, 'store']);
Route::get("/products/{id}", [ProductController::class, 'show']);
Route::put("/products/{id}", [ProductController::class, 'update']);
Route::delete("/products/{id}", [ProductController::class, 'destroy']);

Route::post("/user/register", [AuthController::class, 'register']);
Route::post("/user/login", [AuthController::class, 'login']);
Route::post("/user/logout", [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
