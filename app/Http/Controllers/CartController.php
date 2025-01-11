<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller implements HasMiddleware
{
    // This function sets this controller authorizable
    public static function middleware() {
        return [
            new Middleware('auth:sanctum', except: [])
        ];
    }

    // This function to add request
    public function addToCart(Request $request)
    {
        try {
            $fields = $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'service_cost' => 'sometimes|numeric|min:0'
            ]);

            $cost = 1.00;

            if(isset($fields['service_cost'])) {
                $cost = $fields['service_cost'];
            }
            
            $user = auth('sanctum')->user();
    
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
                    'service_cost' => $cost,
                    'product_id' => $fields['product_id'],
                    'quantity' => $fields['quantity'],
                    'user_id' => $user->id,
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

            if(isset($fields['quantity']) && $fields['quantity'] > $product->quantity + $order->quantity) {
                return response([
                    'message' => 'no enough quantity'
                ], 403);
            }

            if (isset($fields['quantity'])) {
                $dif = $fields['quantity'] - $order->quantity;
                $product->quantity -= $dif; // Decrease or increase product stock accordingly
            }

            $order->fill([
                'deliver_date' => $fields['deliver_date'] ?? $order->deliver_date,
                'location' => $fields['location'] ?? $order->location,
                'quantity' => $fields['quantity'] ?? $order->quantity
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
    static public function getOrders() {
        try {
            $user = auth('sanctum')->user();

            if(!$user) {
                return response([
                    'message' => 'user not found'
                ], 403);
            }

            $cost = 1.00;
            
            $orders = Cart::where('user_id', $user->id)
            ->join('products', 'carts.product_id', '=', 'products.id')
            ->join('stores', 'products.store_id', '=', 'stores.id')
            ->select(
                'carts.quantity',
                          'carts.created_at as order_date',
                          'carts.price as price',
                          'carts.service_cost as service_cost',
                          'products.id as id',
                          'products.name as title',
                          'products.price as product_price',
                          'products.description as description',
                          'products.quantity as product_quantity',
                          'products.store_id as store_id',
                          'stores.store_name as store_name',
                          'products.average_rating as product_average_rating',
                          'products.ingredients as product_ingredients',
                          'products.image_url as imageUrl'
                          )
                          ->get();
            $total = $orders->sum('total_cost') + $cost;
            $total = number_format($total, 2, '.', '');

            if(!$orders) {
                return response([
                    'message' => 'no orders found'
                ], 403);
            }

            return response([
                'orders' => $orders,
                'total' => $total
            ], 200);
        } catch(\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ]);
        }
    }

    // This function to get all user's orders
    static public function getOrdersReturn() {
        try {
            $user = auth('sanctum')->user();

            if(!$user) {
                return response([
                    'message' => 'user not found'
                ], 403);
            }

            $orders = Cart::where('user_id', $user->id)
                      ->join('products', 'carts.product_id', '=', 'products.id')
                      ->join('stores', 'products.store_id', '=', 'stores.id')
                      ->select(
                          'carts.quantity',
                          'carts.created_at as order_date',
                          'carts.price as price',
                          'carts.service_cost as service_cost',
                          DB::raw('carts.price + carts.service_cost as total_cost'),
                          'products.id as id',
                          'products.name as title',
                          'products.price as product_price',
                          'products.description as description',
                          'products.quantity as product_quantity',
                          'products.store_id as store_id',
                          'stores.store_name as store_name',
                          'products.average_rating as product_average_rating',
                          'products.ingredients as product_ingredients',
                          'products.image_url as imageUrl'
                      )
                      ->get();

            if(!$orders) {
                return response([
                    'message' => 'no orders found'
                ], 403);
            }

            return $orders;
        } catch(\Exception $e) {
            return response([
                'message' => $e->getMessage()
            ]);
        }
    }

    // This function pay for the orders (goods in the cart)
    
}
