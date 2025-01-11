<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

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

            if($orders->isEmpty()) {
                return response([
                    'message' => 'no orders found'
                ], 403);
            }

            $log = [
                'user_id' => $user->id,
                'orders' => $orders,
                'status' => 0,
                'location' => $location
            ];

            Log::create($log);

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
        
    }
}
