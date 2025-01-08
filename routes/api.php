<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserInfo;
use App\Http\Controllers\ProductController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Authentication and user profile routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'
])->middleware('auth:sanctum');
Route::post('/upload_profile_image', [UserInfo::class, 'uploadImage'])->middleware('auth:sanctum');
Route::post('/register_complement', [UserInfo::class, 'registerComp']);

// Store routes
Route::post('/create_store', [StoreController::class, 'createStore']);
Route::get('/show_all_stores', [StoreController::class, 'showAllStores']);
Route::post('/show_stores_type', [StoreController::class, 'showStoresType']);


// Products routes
Route::post('/create_product', [ProductController::class, 'createProduct']);
Route::post('/find_product_by_name', [ProductController::class, 'findProductByName']);
Route::post('/find_product_by_store', [ProductController::class, 'findProductByStore']);
Route::post('/update_product', [ProductController::class, 'updateProduct']);

//Route::post('/register_complement', [AuthController::class, 'registerComp']);
Route::match(['get', 'post'], 'show_products', [ProductController::class, 'showProducts']);

//Route::post('/listProducts', [ProductController::class, 'listProducts']);

// Cart routes
Route::post('add_to_cart', [CartController::class, 'addToCart']);
