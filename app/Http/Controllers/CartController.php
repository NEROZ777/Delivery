<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function addToCart(Request $request)
    {
        // التحقق من صحة البيانات المدخلة
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        // الحصول على المنتج بناءً على الـ product_id
        $product = Product::find($validated['product_id']);

        // التحقق من أن الكمية المطلوبة أقل من أو تساوي الكمية المتوفرة
        if ($product->quantity < $validated['quantity']) {
            return response()->json(['message' => 'Not enough stock available.'], 400);
        }

        // الحصول على المستخدم الحالي
        $user = auth('sanctum')->user();

        // التحقق إذا كان المنتج موجود بالفعل في السلة
        $cartItem = Cart::where('product_id', $validated['product_id'])
                        ->where('user_id', $user->id) 
                        ->first();

        if ($cartItem) {
            // إذا كان المنتج موجود بالفعل، نزيد الكمية
            $cartItem->quantity += $validated['quantity'];
            $cartItem->save();
        } else {
            // إذا لم يكن المنتج موجود في السلة، نضيفه
            Cart::create([
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'user_id' => $user->id,
            ]);
        }

        // إنقاص الكمية في جدول المنتجات
        $product->quantity -= $validated['quantity'];
        $product->save();

        return response()->json(['message' => 'Product added to cart successfully.'], 200);
    }
}
