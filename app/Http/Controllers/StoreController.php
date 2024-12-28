<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class StoreController extends Controller implements HasMiddleware
{
    // This function to make this functions autherisable 
    public static function middleware() {
        return [
            new Middleware('auth:sanctum', except: [])
        ];
    }

    // This function to create a new store
    public function createStore(Request $request) {
        try {
            $fields = $request->validate([
                'store_name' => 'required',
                'store_type' => 'required',
                'about' => 'required'
            ]);
    
            $store = Store::create($fields);
    
            return response([
                'message' => 'store creating done correctly',
                'store' => $store
            ], 200);
        } catch(\Exception $e){
            return response([
                'error' => 'store creating problem',
                'message' => $e->getMessage()
            ], 403);
        }
    }
}
