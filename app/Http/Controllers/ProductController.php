<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Stichoza\GoogleTranslate\GoogleTranslate;

class ProductController extends Controller implements HasMiddleware
{
    
    public static function middleware() {
        return [
            new Middleware('auth:sanctum', except: [])
        ];
    }

    
    public function createProduct(Request $request) 
{ 
    try { 
        
        $validated = $request->validate([ 
            'name' => 'required|string|max:255', 
            'description' => 'nullable|string', 
            'price' => 'required|numeric|min:0', 
            'quantity' => 'required|integer|min:1', 
            'store_name' => 'required|string', 
            'ingredients' => 'required|string',  
            'image_url' => 'nullable|url'
        ]); 

        
        $store = Store::where('store_name', $validated['store_name'])->first(); 

        if (!$store) {
            return response()->json([ 
                'error' => 'Store not found', 
            ], 404); 
        }
    
        
        $validated['store_id'] = $store->id;

        $existingProduct = Product::where('name', $validated['name'])
            ->where('store_id', $validated['store_id'])
            ->where('ingredients', $validated['ingredients'])
            ->where('description', $validated['description'])  
            ->where('price', $validated['price'])  
            ->where('image_url', $validated['image_url'])  
            ->first(); 

        if ($existingProduct) { 
            $existingProduct->quantity += $validated['quantity']; 
            $existingProduct->save(); 

            return response()->json([ 
                'message' => 'Product quantity updated successfully', 
               // 'product' => $existingProduct,  
            ], 200); 
        } 
        
        $product = Product::create($validated); 

        return response()->json([ 
            'message' => 'Product created successfully', 
            //'product' => $product,  
        ], 200); 
    } catch(\Exception $e) { 
        return response()->json([ 
            'error' => 'Product creation error', 
            'message' => $e->getMessage(), 
        ], 403); 
    } 
}
public function deleteProduct(Request $request)
{
    try {
        
        $id = $request->input('id');

        
        if (!$id) {
            return response()->json([
                'error' => 'Product ID is required',
            ], 400);
        }

        
        $product = Product::find($id);


        if (!$product) {
            return response()->json([
                'error' => 'Product not found',
            ], 404);
        }

        
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Product deletion error',
            'message' => $e->getMessage(),
        ], 403);
    }
}




    // This function to update the product
    public function updateProduct(Request $request)
    {
        try {
            
            $validated = $request->validate([
                'id' => 'required|exists:products,id', 
                'name' => 'required|string|max:255|unique:products,name,', 
                'description' => 'nullable|string', 
                'price' => 'required|numeric|min:0', 
                'quantity' => 'required|integer|min:1', 
                'store_id' => 'required',
                'ingredients' => 'required',
                'image_url' => 'nullable|url',
            ]);
    
            $productId = $validated['id'];
    

            $product = Product::findOrFail($productId);
    

            $product->update($validated);
    

            return response()->json([
                'message' => 'Product updated successfully',
                // 'product' => $product,  
            ], 200);
        } catch (\Exception $e) {
            
            return response()->json([
                'error' => 'Product updating error',
                'message' => $e->getMessage(),
            ], 403);
        }
    }
    
    // This function to find a product by name
    public function findProductByName(Request $request) {
        try {
            // 
            $fields = $request->validate([
                'product_name' => 'required',
                'lang' => 'nullable|string|max:5',  
            ]);
    
            
            $language = $fields['lang'] ?? 'en';
    
            
            $product = Product::where('name', 'like', '%' . $fields['product_name'] . '%')
                                ->orderByRaw('COALESCE(average_rating, 0) DESC')
                                ->get();
    
            
            if ($product->isEmpty()) {
                $message = GoogleTranslate::trans('no products has found', $language);  
                return response([
                    'message' => $message,
                ], 403);
            }
    

            $message = GoogleTranslate::trans('the product has found', $language);
    
            
            $translatedProducts = $product->map(function ($item) use ($language) {
                return [
                    'id' => $item->id,
                    'title' => GoogleTranslate::trans($item->name, $language),  
                    'description' => GoogleTranslate::trans($item->description, $language),  
                    'ingredients' => GoogleTranslate::trans($item->ingredients, $language),  
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'store_name' =>GoogleTranslate::trans($item->store->store_name),
                    'average_rating' => $item->average_rating,
                    'image_url' => $item->image_url,
                 //   'created_at' => $item->created_at,
                   // 'updated_at' => $item->updated_at,
                  //  'image' => $item->image,
                ];
            });
    
            return response([
                //'message' => $message,
                'product' => $translatedProducts,  
            ], 200);
        } catch (\Exception $e) {
            
            $errorMessage = GoogleTranslate::trans('error happened while searching for the product', $fields['lang'] ?? 'en');
            return response([
                'error' => $errorMessage,
                'message' => $e->getMessage(),
            ], 403);
        }
    }
    

    // The function to find a product by name and store name
    

    public function findProductByStore(Request $request) {
        try {
            $fields = $request->validate([
                'product_name' => 'required',
               // 'store_id' => 'required',
                'lang' => 'nullable|string|max:5',
            ]);
    
            $language = $fields['lang'] ?? 'en';
    
            $product = Product::where('store_id', $fields['store_id'])
            ->orderByRaw('COALESCE(average_rating, 0) DESC')    
                ->where('name', 'like', '%' . $fields['product_name'] . '%')->get();
    
            
            if ($product->isEmpty()) {
                $message = GoogleTranslate::trans('no products has found', $language);
                return response([
                    'message' => $message,
                ], 403);
            }
    
            
            $message = GoogleTranslate::trans('the product has found', $language);
    
            
            $formatedProducts = $product->map(function($item) use ($language) {
                return [
                    'id' => $item->id,
                    'title' => $language != 'en' ? GoogleTranslate::trans($item->name, $language) : $item->name,
                    'description' => $language != 'en' ? GoogleTranslate::trans($item->description, $language) : $item->description,
                    'ingredients' => $language != 'en' ? GoogleTranslate::trans($item->ingredients, $language) : $item->ingredients,
                    'price' => number_format($item->price, 2) . ' $',
                    'quantity' => $item->quantity,
                    'store_id' => $item->store_id,
                    'average_rating' => $item->average_rating,
                    'image_url' => $item->image_url,
                ];
            });
    
            return response([
             //   'message' => $message,
                'product' => $formatedProducts,  
            ], 200);
        } catch (\Exception $e) {
            
            $errorMessage = GoogleTranslate::trans('error happened while searching for the product', $fields['lang'] ?? 'en');
            return response([
                'error' => $errorMessage,
                'message' => $e->getMessage(),
            ], 403);
        }
    }
    
    
   

public function ShowProductByStore(Request $request) {
    try {
        $fields = $request->validate([
            'store_name' => 'required',
            'lang' => 'nullable|string',  
        ]);

        
        $language = $fields['lang'] ?? 'en';

        
        $store = Store::where('store_name', $fields['store_name'])->first();

        if (!$store) {
            return response([
                'message' => 'Store not found',
            ], 404);
        }

        
        $products = Product::where('store_id',  $store->id)->get();

        if($products->isEmpty()) {
            return response([
                'message' => 'No products found',
            ], 403);
        }

        
        $formatedProducts = $products->map(function($product) use ($store, $language) {
            $tr = new GoogleTranslate($language);  

            return [
                'title' => $tr->translate($product->name),  
                'description' => $tr->translate($product->description),  
                'price' => number_format($product->price, 2) . ' $', 
                'imageUrl' => $product->image_url,
                'id' => $product->id,
                'ingredients' => $product->ingredients,
                'average_rating' => $product->average_rating,
                'store_id' => $product->store_id,
                'quantity' => $product->quantity,
                'store_name' => $store->store_name,
            ];     
        });

        return response([
            'data' => $formatedProducts
        ], 200);

    } catch(\Exception $e) {
        return response([
            'success' => false,
            'error' => 'Something happened while showing products',
            'message' => $e->getMessage()
        ], 403);
    }
}


    


public function showProducts(Request $request) {
    try {
        
        $fields = $request->validate([
            'lang' => 'nullable|string',  
        ]);

        
        $language = $fields['lang'] ?? 'en';

        
        $products = Product::with('store')
                    ->orderByRaw('COALESCE(average_rating, 0) DESC')
                    ->take(min(10, Product::count()))  
                    ->get();

        
        $formatedProducts = $products->map(function($product) use ($language) {
            $tr = new GoogleTranslate($language);  

            return [
                'title' => $tr->translate($product->name),   
                'description' => $tr->translate($product->description),  
                'price' => number_format($product->price, 2) . ' $', 
                'imageUrl' => $product->image_url,
                'id' => $product->id,
                'ingredients' => $product->ingredients,
                'average_rating' => $product->average_rating,
                'store_id' => $product->store->id,
                'quantity' => $product->quantity,
                'store_name' => $product->store->store_name,
                'store_id'=>$product->store->id
            ];     
        });

        return response([
            'data' => $formatedProducts
        ], 200);

    } catch(\Exception $e) {
        return response([
            'success' => false,
            'error' => 'Something happened while showing products',
            'message' => $e->getMessage()
        ], 403);
    }
}

    

}