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
            new Middleware('auth:sanctum', except: ['showAllStores'])
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

    // This function to show the all stores
    public function showAllStores(Request $request){
        try {
            $stores = Store::all();
    
            return response([
                $stores
            ], 200);
        } catch(\Exception $e) {
            return response([
                'error' => 'something happend with stores showing',
                'message' => $e->getMessage()
            ], 403);
        }
    }

    // This function to update an excesting store
    public function storeUpdate(Request $request) {
        $fields = $request->validate([
            'store_id' => 'required',
            'store_name' => 'required',
            'store_type' => 'required',
            'about' => 'required'
        ]);

        $store = STORE::where('id', $fields['store_id'])->first();

        if(!$store){
            return response([
                'error' => 'store not found'
            ], 403);
        }

        $store->update($fields);

        return response([], 200);
    }
}
