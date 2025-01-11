<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Stichoza\GoogleTranslate\GoogleTranslate;

class StoreController extends Controller implements HasMiddleware
{
    
    public static function middleware() {
        return [
            new Middleware('auth:sanctum', except: ['showAllStores'])
        ];
    }

    
    public function createStore(Request $request) {
        try {
            $fields = $request->validate([
                'store_name' =>  'required|unique:stores,store_name',
                'store_type' => 'required',
                'store_image' => 'required',
                'likes' => 'required',
                'location' => 'required',
                'cuisine' => 'required',
                'dishes' => 'required'
            ]);
    
            $store = Store::create($fields);
    
            return response([
                'message' => 'store creating done correctly'
                // 'store' => $store
            ], 200);
        } catch(\Exception $e){
            return response([
                'error' => 'store creating problem',
                'message' => $e->getMessage()
            ], 403);
        }
    }
    public function deleteStore(Request $request)
    {
        try {
          
            $id = $request->input('id');
    
          
            if (!$id) {
                return response()->json([
                    'error' => 'Store ID is required',
                ], 400);
            }
    
            $store = Store::find($id);
    
            if (!$store) {
                return response()->json([
                    'error' => 'Store not found',
                ], 404);
            }
    
            $store->delete();
    
            return response()->json([
                'message' => 'Store deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Store deletion error',
                'message' => $e->getMessage(),
            ], 403);
        }
    }
    


    
    public function showAllStores(Request $request) {
        try {
            $stores = Store::all();
            
            $language = $request->input('lang', 'en');   
    

            $translatedStores = $stores->map(function ($store) use ($language) {
                return [
                    'id' => $store->id,
                    'name' => $store->name ? GoogleTranslate::trans($store->name, $language) : '',  
                    'description' => $store->description ? GoogleTranslate::trans($store->description, $language) : '',  
                    'store_type' => $store->store_type ? GoogleTranslate::trans($store->store_type, $language) : '',
                    'created_at' => $store->created_at,
                    'updated_at' => $store->updated_at,
                ];
            });
    
            return response([
                'stores' => $translatedStores,
            ], 200);
        } catch (\Exception $e) {
            return response([
                'error' => 'something happened with stores showing',
                'message' => $e->getMessage(),
            ], 403);
        }
    }
    
    
    public function showStoresType(Request $request) {
        try {
            $type = $request->input('type');
            
            $stores = Store::where('store_type', $type)->get();
    
            $language = $request->input('lang', 'en');  
    
            
            $translatedStores = $stores->map(function ($store) use ($language) {
                return [
                    'id' => $store->id,
                    'name' => $store->name ? GoogleTranslate::trans($store->name, $language) : '',  
                    'description' => $store->description ? GoogleTranslate::trans($store->description, $language) : '',  
                    'store_type' => $store->store_type ? GoogleTranslate::trans($store->store_type, $language) : '', 
                    'created_at' => $store->created_at,
                    'updated_at' => $store->updated_at,
                ];
            });
    
            return response([
                'stores' => $translatedStores,
            ], 200);
    
        } catch (\Exception $e) {
            return response([
                'error' => 'something happened with stores showing',
                'message' => $e->getMessage(),
            ], 403);
        }
    }
    public function findStoreByName(Request $request) {
        try {
            
            $fields = $request->validate([
                'store_name' => 'required', 
                'lang' => 'nullable|string|max:5',  
            ]);
    
            
            $language = $fields['lang'] ?? 'en';
    
            
            $stores = Store::where('store_name', 'like', '%' . $fields['store_name'] . '%')
                            ->orderByRaw('COALESCE(store_rate, 0) DESC')
                            ->get();
    
            
            if ($stores->isEmpty()) {
                $message = GoogleTranslate::trans('no stores found', $language);  
                return response([
                    'message' => $message,
                ], 403);
            }
    
            
            $message = GoogleTranslate::trans('the store has been found', $language);
    

            $translatedStores = $stores->map(function ($item) use ($language) {
                return [
                    'store_name' => $item->store_name,
                    'store_type' => $item->store_type,
                    'store_image' => $item->store_image,
                    'likes' => $item->likes,
                    'location' => $item->location,
                    'cuisine' => $item->cuisine,
                    'dishes' => $item->dishes,
                    'average_rating' => $item->average_rating,
                    'image_url' => $item->image_url,
                   // 'image' => $item->image,
                ];
            });
    
            return response([
                'message' => $message,
                'store' => $translatedStores,  
            ], 200);
    
        } catch (\Exception $e) {
            $errorMessage = GoogleTranslate::trans('error happened while searching for the store', $fields['lang'] ?? 'en');
            return response([
                'error' => $errorMessage,
                'message' => $e->getMessage(),
            ], 403);
        }
    }
    
    
}
