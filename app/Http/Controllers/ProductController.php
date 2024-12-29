<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
class ProductController extends Controller
{
    public function index()
    {
        $products = Product::all();
        return response()->json($products);
    }


    //
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'image' => 'nullable|url',
        ]);
    

        $product = Product::where('name', $validated['name'])
                         ->where('price', $validated['price'])
                         ->first();
    
        if ($product) {
            $product->quantity += $validated['quantity'];
            $product->save();
            return response()->json([
                'message' => 'Product quantity updated successfully',
                'product' => $product,
            ], 200);
        } else {
            $product = Product::create($validated);
            return response()->json([
                'message' => 'Product created successfully',
                'product' => $product,
            ], 201);
        }
    }
    
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'quantity' => 'required|integer',
            'image' => 'nullable|url', 
        ]);

       $p= $product->update($validated);

        
        return response()->json([
            'message' => 'Product created successfully',
            'product' => $p,
        ], 201);
    }
}
