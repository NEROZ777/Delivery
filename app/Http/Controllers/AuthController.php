<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\UltraMsgService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    protected $ultraMsgService;

    public function __construct(UltraMsgService $ultraMsgService)
    {
        $this->ultraMsgService = $ultraMsgService;
    }

    public function register(Request $request)
    {
        try {
            $fields = $request->validate([
                'first_name' => 'required|min:2|max:255',
                'last_name' => 'required|min:2|max:255',
                'email' => 'required|email|unique:users',
                'phone_number' => 'required|numeric|regex:/^09\d{8}$/|unique:users',
                'password' => 'required|min:6|confirmed',
                'location' => 'sometimes|min:1'
            ]);

            
            $user = User::create($fields);

            
            $verificationCode = rand(1000, 9999);

            
            VerificationCode::create([
                'user_id' => $user->id,
                'code' => $verificationCode,
                'phone_number' => $user->phone_number,
                'used' => false
            ]);

            
            $message = "Your verification code is: $verificationCode";
            $phoneNumber = preg_replace('/[^0-9+]/', '', $user->phone_number);

            $phoneNumber = '+963' . substr($phoneNumber, 1);  //
            $this->ultraMsgService->sendMessage($phoneNumber, $message);

            $token = $user->createToken($request->first_name);

            return response([
                'message' => 'SignUp done',
                'token' => $token->plainTextToken
            ], 200);

        } catch (\Exception $e) {
            return response([
                'error' => 'Registration failed',
                'message' => $e->getMessage()
            ], 403);
        }
    }

    
    public function login(Request $request)
    {
        try {
            $fields = $request->validate([
                'phone_number' => 'required',
                'password' => 'required'
            ]);

            $user = User::where('phone_number', $request->phone_number)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'error' => 'Login error',
                    'message' => 'The provided credentials are incorrect.'
                ], 403);
            }

            $user->tokens()->delete();
            $token = $user->createToken($user->first_name);

            return response([
                'message' => 'Login successful',
                'token' => $token->plainTextToken
            ], 200);

        } catch (\Exception $e) {
            return response([
                'error' => 'Login error',
                'message' => $e->getMessage()
            ], 403);
        }
    }
    
    
    public function logout(Request $request)
    {
        try {
            $request->user()->tokens()->delete();

            return response([
                'message' => 'You are logged out.'
            ], 200);

        } catch (\Exception $e) {
            return response([
                'error' => 'Logout error',
                'message' => $e->getMessage()
            ], 403);
        }
    }
 

    public function testSendMessage()
    {
        
        $phoneNumber = '0953933942';
        $phoneNumber = preg_replace('/[^0-9+]/', '', $phoneNumber);
        $phoneNumber = '+963' . substr($phoneNumber, 1);

        
        $message = 'هذه رسالة اختبار عبر WhatsApp باستخدام UltraMsg.';

        
        $response = $this->ultraMsgService->sendMessage($phoneNumber, $message);
dd($response);
    } 
}

