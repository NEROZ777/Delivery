<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // This function to add request
    public function addToCart(Request $request)
    {
        try {
            $fields = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'deliver_date' => 'required',
                'location' => 'sometimes'
            ]);
            
            $user = auth('sanctum')->user();

            if(!isset($fields['location'])) {
                $fields['location'] = $user->location;
            }
    
            $product = Product::find($fields['product_id']);
    
            if ($product->quantity < $fields['quantity']) {
                return response()->json([
                    'message' => 'Not enough stock available.'], 403);
            }
    
    
            $order = Cart::where('product_id', $fields['product_id'])
                            ->where('user_id', $user->id) 
                            ->first();
    
            if ($order) {
                $order->quantity += $fields['quantity'];
                $order->price = $order->quantity * $product->price;
                $order->save();
            } else {
                $price = $product->price * $fields['quantity'];
                Cart::create([
                    'product_id' => $fields['product_id'],
                    'quantity' => $fields['quantity'],
                    'user_id' => $user->id,
                    'location' => $fields['location'],
                    'price' => $price
                ]);
            }
    
            $product->quantity -= $fields['quantity'];
            $product->save();
    
            return response()->json([
                'message' => 'Product added to cart successfully.'], 200);
        } catch(\Exception $e) {
            return response()->json([
                'message' => 'request faild',
                'error' => $e->getMessage()], 403);
        } 
    }

    // This function to edit a request
    public function updateOrder(Request $request)
    {
        try {
            $fields = $request->validate([
                'order_id' => 'required|exists:carts,id',
                'quantity' => 'sometimes|integer|min:1', // Ensure quantity is positive
                'deliver_date' => 'sometimes',
                'quantity_type' => 'required_if:quantity,true|in:increase,decrease',
                'location' => 'sometimes'
            ]);

            $order = Cart::find($fields['order_id']);

            if(!$order) {
                return response([
                    'message' => ' can not find the order!'
                    , 403
                ]);
            }

            $product = Product::find($order->product_id);

            if(!$product) {
                return response([
                    'message' => ' can not find the product!'
                    , 403
                ]);
            }

            if(isset($fields['quantity'])) {
                switch($fields['quantity_type']) {
                    case 'increase': 
                        $newQuantity = $order->quantity + $fields['quantity'];
                        if($newQuantity > $product->quantity) {
                            return response([
                                'message' => ' can not add, no enough quantity remain!'
                                , 403
                            ]); 
                        }

                        $order->quantity = $newQuantity;// Increase the order's quantity

                        $product->quantity -= $fields['quantity'];// Decrease the product's quantity

                        break;
                    case 'decrease':
                        $newQuantity = $order->quantity - $fields['quantity'];
                        $order->quantity = $newQuantity;// Dencrease the order's quantity

                        $product->quantity += $fields['quantity'];// Increase the product's quantity

                        break;
                }
            }

            $order->fill([
                'deliver_date' => $fields['deliver_date'] ?? $order->deliver_date,
                'location' => $fields['location'] ?? $order->location
            ]);

            $order->save();
            $product->save();

            return response([
                'message' => 'order updated!',
                200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'order did not updated',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteOrder(Request $request) {
        try {
            $fields = $request->validate([
                'order_id' => 'required'
            ]);

            $order = Cart::find($fields['order_id']);

            if(!$order) {
                return response([
                    'message' => 'cannot find the order'
                ], 403);
            }

            $order->delete();

            return response([
                'message' => 'order cancled'
            ], 200);
        } catch(\Exception $e) {
            return response([
                'message' => 'order did not cancled',
                'error' => $e->getMessage()
            ], 500);
        } 
    }

    // This function to get all user's orders
    public function getOrders(Request $request) {
        try {
            $user = auth('sanctum')->user();

            if(!$user) {
                return response([
                    'message' => 'user not found'
                ], 403);
            }

            $orders = Cart::where('user_id', $user->id)->get();

            if(!$orders) {
                return response([
                    'message' => 'no orders found'
                ], 403);
            }

            return response([
                'orders' => $orders
            ], 200);
        } catch(\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ]);
        }
    }

    // This function pay for the orders (goods in the cart)
    public function pay(Request $request) {
        try {
            $fields = CartController::getOrders($request);
    
            $orders = $fields['orders'];

            if(!$orders) {
                return response([
                    'message' => 'no orders has found'
                ], 403);
            }
    
            $totalPrice = 0;
    
            foreach($orders as $order) {
                $totalPrice += $order->price;
            }
        } catch(\Exception $e) {

        }
    }
}
