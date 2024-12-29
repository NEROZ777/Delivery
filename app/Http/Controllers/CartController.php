<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $user = auth()->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $cart = $user->cartItems()->first();  

        if (!$cart) {
            
            $cart = Cart::create([
                'user_id' => $user->id,
            ]);
        }


        $cartItem = $cart->products()->where('product_id', $validated['product_id'])->first();

        if ($cartItem) {
            $cartItem->pivot->quantity += $validated['quantity'];
            $cartItem->pivot->save();
        } else {
            $cart->products()->attach($validated['product_id'], ['quantity' => $validated['quantity']]);
        }

        return response()->json(['message' => 'Product added to cart successfully.'], 200);
    }
}
