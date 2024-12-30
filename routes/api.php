<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\UserInfo;
use App\Http\Controllers\ProductController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'
])->middleware('auth:sanctum');
Route::post('/upload_profile_image', [UserInfo::class, 'uploadImage'])->middleware('auth:sanctum');
Route::post('/create_store', [StoreController::class, 'createStore']);
Route::get('/show_all_stores', [StoreController::class, 'showAllStores']);
Route::post('/create_product', [ProductController::class, 'createProduct']);
Route::post('/find_product_by_name', [ProductController::class, 'findProductByName']);
