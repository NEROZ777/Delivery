<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
class CartController extends Controller
{
    //
    public function addToCart(Request $request)
{
    $validated = $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1',
    ]);

    
    $cartItem = Cart::where('product_id', $validated['product_id'])
                    ->where('user_id', $request->user_id) 
                    ->first();

    if ($cartItem) {
        $cartItem->quantity += $validated['quantity'];
        $cartItem->save(); 
    } else {
        Cart::create([
            'product_id' => $validated['product_id'],
            'quantity' => $validated['quantity'],
            'user_id' => $request->user_id, 
        ]);
    }
 
    return response()->json(['message' => 'Product added to cart successfully.'], 200);
}
    
}
