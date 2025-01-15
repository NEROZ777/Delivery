<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

use function PHPUnit\Framework\isEmpty;

class LogController extends Controller implements HasMiddleware
{
    // This function sets this controller authorizable
    public static function middleware() {
        return [
            new Middleware('auth:sanctum', except: [])
        ];
    }

    // This function to insure the orders and pay for the cart
    public function pay(Request $request) {
        try {
            $user = auth('sanctum')->user();

            if(!$user) {
                return response([
                    'message' => 'unauthorized'
                ], 401);
            }

            $fields = $request->validate([
                'location' => 'sometimes|min:1'
            ]);

            $location = $fields['location'] ?? $user->location;



            $orders = CartController::getOrdersReturn();

            if(isset($ordersData['orders']) && $ordersData['orders']->isEmpty()) {
                return response([
                    'message' => 'no orders found'
                ], 403);
            }

            $log = [
                'user_id' => $user->id,
                'total_price' => $orders['total'],
                'orders' => $orders['orders'],
                'status' => 0,
                'location' => $location
            ];

            Log::create($log);

            CartController::clearUserCart($user->id);

            return response([
                'message' => 'pay done, waiting for accept'
            ], 200);
        } catch(\Exception $e) {
            return response([
                'message' => 'pay denied',
                'error' => $e->getMessage()
            ], 403);
        }
    }

    // This function to get the invoice
    public function invoice() {
        try {

        } catch(\Exception $e) {

        }
    }

    // This function is for admins to inusre the orders, status:
    // 0 -> 1 (wait -> your order accepted and wait for delivery)
    // 1 -> 2 (delivery -> your order deliverd)
    public function updateOrderStatus(Request $request) {
        try {
            $fields = $request->validate([
                'order_id' => 'required',
                'status' => 'required|in:0, 1, 2'
            ]);

            $order = Log::find($fields['order_id']);

            if(!$order) {
                return response([
                    'message' => 'order didn\'t found'
                ], 403);
            }

            $order->status = $fields['status'];
            $order->save();

            return response([
                'message' => 'order status updated'
            ], 200);
        } catch(\Exception $e) {
            return response([
                'message' => 'cannot update order status',
                'error' => $e->getMessage()
            ], 403);
        }
    }

    // This function to get all orders
    public function getAllOrders() {
        try {
            $orders = Log::all();

            if($orders->isEmpty()) {
                return response([
                    'message' => 'no orders has found'
                ], 403);
            }

            return response([
                'orders' => $orders
            ],200);
        } catch(\Exception $e) {
            return response([
                'message' => 'something happened',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // This function to get all orders which has a specific status number
    public function getAllOrdersByStatus(Request $request) {
        try {
            $fields = $request->validate([
                'status' => 'required|integer|min:0'
            ]);

            $orders = Log::where('status', $fields['status'])->get();

            if($orders->isEmpty()) {
                return response([
                    'message' => 'no orders has found'
                ], 403);
            }

            return response([
                'orders' => $orders
            ],200);
        } catch(\Exception $e) {
            return response([
                'message' => 'something happened',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // This function to cancel an order
    public function cancelOrder(Request $request) {
        try {
            $fields = $request->validate([
                'order_id' => 'required|exists:logs,id'
            ]);
    
            $order = Log::find($fields['order_id']);
            
            if (!$order) {
                return response([
                    'message' => 'The order was not found.'
                ], 404);
            }
    
            $orderItems = json_decode($order->orders, true);
    
            foreach ($orderItems as $item) {
                $product = Product::find($item['id']);
    
                if ($product) {
                    $product->quantity += $item['quantity'];
                    $product->save();
                }
            }
    
            $order->delete();
    
            return response([
                'message' => 'The order has been canceled successfully.'
            ], 200);
    
        } catch (\Exception $e) {
            return response([
                'message' => 'Failed to cancel the order.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    
}
