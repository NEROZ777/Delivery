<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
<<<<<<< HEAD
use App\Http\Controllers\StoreController;
=======
use App\Http\Controllers\CartController;
>>>>>>> 29879969736225a0a703cef765ec88530dbcfaeb
use App\Http\Controllers\UserInfo;
use App\Http\Controllers\ProductController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Authentication and user profile routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'
])->middleware('auth:sanctum');
Route::post('/upload_profile_image', [UserInfo::class, 'uploadImage'])->middleware('auth:sanctum');
<<<<<<< HEAD

// Store routes
Route::post('/create_store', [StoreController::class, 'createStore']);
Route::get('/show_all_stores', [StoreController::class, 'showAllStores']);

// Products routes
Route::post('/create_product', [ProductController::class, 'createProduct']);
Route::post('/find_product_by_name', [ProductController::class, 'findProductByName']);
Route::post('/find_product_by_store', [ProductController::class, 'findProductByStore']);
=======
Route::post('/products/store', [ProductController::class, 'store']);
Route::post('/products/updata', [ProductController::class, 'updata']);
Route::post('cart/add', [CartController::class, 'addToCart']);
>>>>>>> 29879969736225a0a703cef765ec88530dbcfaeb
