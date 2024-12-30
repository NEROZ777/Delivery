<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ProductController extends Controller implements HasMiddleware
{
    // This function to make this functions autherisable 
    public static function middleware() {
        return [
            new Middleware('auth:sanctum', except: [])
        ];
    }

    // This function to create a product
    public function createProduct(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'store_id' => 'required'
        ]);

        try {

            $product = Product::create($validated);
    
            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product,
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'error' => 'product creation error',
                'message' => $e->getMessage(),
            ], 403);
        }
    }

    // This function to update the product
    public function updateProduct(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric',
                'quantity' => 'required|integer',
                'image' => 'nullable|url', 
            ]);
    
           $p = $product->update($validated);
    
            return response()->json([
                'message' => 'Product created successfully',
                'product' => $p,
            ], 200);
        } catch(\Exception $e) {
            return response()->json([
                'error' => 'product updating error',
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
                'product' => $product,
            ], 200);
        } catch(\Exception $e) {
            return response([
                'error' => 'error happend while searchin for the product',
                'message' => $e->getMessage(),
            ], 403);
        }
    }
}
