<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    // This function to make this functions authorisable.
    public static function middleware() {
        return [
            new Middleware('auth:sanctum', except: [])
        ];
    }

    // This function to create a product
    public function createProduct(Request $request) 
{ 
    try { 
        // التحقق من صحة البيانات
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
            $fields = $request->validate([
                'product_name' => 'required'
            ]); 

            $product = Product::where('name', 'like', '%' . $fields['product_name'] . '%')->get();

            if(!$product) {
                return response([
                    'message' => 'no products has found',
                ], 403);
            }

            return response([
                'message' => 'the product has found',
                // 'product' => $product,
            ], 200);
        } catch(\Exception $e) {
            return response([
                'error' => 'error happend while searchin for the product',
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    // The function to find a product by name and store name
    public function findProductByStore(Request $request) {
        try {
            $fields = $request->validate([
                'product_name' => 'required',
                'store_id' => 'required'
            ]); 

            // $store = Store::where('store_name', $fields['store_name'])->first();

            // if(!$store) {
            //     return response([
            //         'message' => 'the store has not found',
            //     ], 403);
            // }

            $product = Product::where('store_id', $fields['store_id'])
                ->where('name', 'like', '%' . $fields['product_name'] . '%')->get();

            if($product->isEmpty()) {
                return response([
                    'message' => 'no products has found',
                ], 403);
            }

            return response([
                'message' => 'the product has found',
                'product' => $product,
            ], 200);
        } catch(\Exception $e) {
            return response([
                'error' => 'error happend while searchin for the product',
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    public function ShowProductByStore(Request $request) {
        try {
            $fields = $request->validate([
                'store_name' => 'required'
            ]);
    

            $store = Store::where('store_name', $fields['store_name'])->first();
    
            if (!$store) {
                return response([
                    'message' => 'Store not found',
                ], 404);
            }
     

            $products = Product::where('store_id',  $store->id)->get();

            if($products->isEmpty()) {
                return response([
                    'message' => 'no products has found',
                ], 403);
            }
            $formatedProducts=$products->map(function($product)use ($store){
                
                return[
                        'title' => $product->name,
                        'description' => $product->description,
                        'price' => number_format($product->price, 2) . ' $', 
                        'imageUrl' =>$product->image_url,
                        'id'=>$product->id,
                        'ingredients'=>$product->ingredients ,
                        'average_rating' => $product->average_rating,
                       'store_id' => $product->store_id,
                       'quantity'=> $product->quantity,
                       'store_name' => $store->store_name,
                        
                ];     
        
        
               });
               
               
                return response([
        
                    // 'success'=>true,
                    'data'=>$formatedProducts
        
                     
                ],200);
        
            }
            catch(\Exception $e){
        return response([
        
        'success'=>false,
        'error'=>'something happened with products showing',
        'message'=>$e->getMessage()
        
        
        ],403);
        
        
        
            }

  
    }


    
    public function showProducts() {
        // $products = Product::orderBy('id', 'asc')->paginate(10);
        // return response()->json($products);
    try{
        $products = Product::with('store')
                    ->orderByRaw('COALESCE(average_rating, 0) DESC')
                   ->take(min(10, Product::count()))  // 
                   ->get();

       
       $formatedProducts=$products->map(function($product){
        return[
                'title' => $product->name,
                'description' => $product->description,
                'price' => number_format($product->price, 2) . ' $', 
                'imageUrl' =>$product->image_url,
                'id'=>$product->id,
                'ingredients'=>$product->ingredients ,
                'average_rating' => $product->average_rating,
               'store_id' => $product->store_id,
               'quantity'=> $product->quantity,
               'store_name' => $product->store->store_name
                
        ];     


       });
       
       
        return response([

            // 'success'=>true,
            'data'=>$formatedProducts

             
        ],200);

    }
    catch(\Exception $e){
return response([

'success'=>false,
'error'=>'something happened with products showing',
'message'=>$e->getMessage()


],403);



    }
    
    
    }
    

}