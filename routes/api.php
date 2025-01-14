<?php

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\UserInfo;
use App\Http\Controllers\ProductController;
use App\Services\UltraMsgService;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\ProductRatingController1;
use App\Http\Controllers\StoreRatingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Authentication and user profile routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/verify_code', [AuthController::class, 'verifyCode']);
Route::post('/reset_password', [AuthController::class, 'verifyCode'])->middleware('auth:sanctum');
Route::get('/resend_code', [AuthController::class, 'resendCode'])->middleware('auth:sanctum');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'
])->middleware('auth:sanctum');
Route::post('/upload_profile_image', [UserInfo::class, 'uploadImage'])->middleware('auth:sanctum');
Route::post('/register_complement', [UserInfo::class, 'registerComp']);
Route::post('/user_info', [UserInfo::class, 'userInfo']);
Route::post('verify_code', [AuthController::class, 'verifyCode'])->middleware('auth:sanctum');
Route::post('/test-send-message', [AuthController::class, 'testSendMessage']);
Route::post('/edit_profile', [UserInfo::class, 'editProfile']);


// Store routes
Route::post('/create_store', [StoreController::class, 'createStore']);
Route::delete('/delete_store', [StoreController::class, 'deleteStore']);
Route::post('/show_all_stores', [StoreController::class, 'showAllStores']);
Route::post('/show_stores_type', [StoreController::class, 'showStoresType']);// انت بتعطيه النوع و هو برجع الستورز من نفس النوع 
Route::post('/find_store_by_name', [StoreController::class, 'findStoreByName']);
// Route::match(['get', 'post'], '/show_stores_type', [StoreController::class, 'showStoresType']);

// Products routes
Route::post('/create_product', [ProductController::class, 'createProduct']);
Route::delete('/delete_product', [ProductController::class, 'deleteProduct']);
Route::post('/find_product_by_name', [ProductController::class, 'findProductByName']);
Route::post('/find_product_by_store', [ProductController::class, 'findProductByStore']);
Route::post('/update_product', [ProductController::class, 'updateProduct']);
Route::match(['get', 'post'], 'show_products', [ProductController::class, 'showProducts']);//تابع بيعرض المنتجات بيعرض حسب ال id 
Route::post('/show_product_by_store', [ProductController::class, 'ShowProductByStore']);


// Cart routes
Route::post('add_to_cart', [CartController::class, 'addToCart']);
Route::post('/update_order', [CartController::class, 'updateOrder']);
Route::post('/delete_order', [CartController::class, 'deleteOrder']);
Route::get('/get_orders', [CartController::class, 'getOrders']);


// Favourites routes
Route::post('/add_to_favourite', [UserInfo::class, 'addToFav']);
Route::post('/remove_from_favourite', [UserInfo::class, 'removeFav']);
Route::post('/favourite_products', [UserInfo::class, 'getAllFavProducts']);
Route::post('/favourite_stores', [UserInfo::class, 'getAllFavStores']);


// ratings routes
Route::post('/product_rating', [ProductRatingController1::class, 'store'])->middleware('auth:sanctum');
Route::post('/store_rating', [StoreRatingController::class, 'storeing'])->middleware('auth:sanctum');
Route::post('/destroy_product_rating', [ProductRatingController1::class, 'destroy'])->middleware('auth:sanctum');
Route::post('/destroy_stor_rating', [StoreRatingController::class, 'destroy'])->middleware('auth:sanctum');


// Logs routes
Route::post('/pay', [LogController::class, 'pay']);
Route::post('/update_order_status', [LogController::class, 'updateOrderStatus']);
Route::post('/get_log_orders', [LogController::class, 'getAllOrders']);
Route::post('/get_log_status_orders', [LogController::class, 'getAllOrdersByStatus']);