<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerificationCode;
use App\Services\UltraMsgService;
//use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected $ultraMsgService;

    public function __construct(UltraMsgService $ultraMsgService)
    {
        $this->ultraMsgService = $ultraMsgService;
    }

    // public function register(Request $request)
    // {
    //     try {
    //         $fields = $request->validate([
    //             'first_name' => 'required|min:2|max:255',
    //             'last_name' => 'required|min:2|max:255',
    //             'email' => 'required|email|unique:users',
    //             'phone_number' => 'required|numeric|regex:/^09\d{8}$/|unique:users',
    //             'password' => 'required|min:6|confirmed',
    //             'location' => 'sometimes|min:1',
    //             'profile_image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif|max:15000'
    //         ]);

    //         if ($request->hasFile('profile_image')) {
    //             $fields['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
    //         }

    //         $user = User::create($fields);
    //         $verificationCode = mt_rand(1000, 9999);
    //             $user ->code=$verificationCode;
                
    //         // VerificationCode::create([
    //         //     'user_id' => $user->id,
    //         //     'code' => $verificationCode,
    //         //     'phone_number' => $user->phone_number,
    //         //     'used' => false
    //         // ]);

    //         $message = "mr. $user->first_name your verification code is: $verificationCode";
    //         $phoneNumber = '+963' . substr(preg_replace('/[^0-9+]/', '', $user->phone_number), 1);

    //         $this->ultraMsgService->sendMessage($phoneNumber, $message);

    //         $token = $user->createToken($request->first_name);

    //         return response([
    //             'message' => 'SignUp done',
    //             'token' => $token->plainTextToken
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response([
    //             'error' => 'Registration failed',
    //             'message' => $e->getMessage()
    //         ], 403);
    //     }
    // }
    public function register(Request $request)
{
    try {
        $fields = $request->validate([
            'first_name' => 'required|min:2|max:255',
            'last_name' => 'required|min:2|max:255',
            'email' => 'required|email|unique:users',
            'phone_number' => 'required|numeric|regex:/^09\d{8}$/|unique:users',
            'password' => 'required|min:6|confirmed',
            'location' => 'sometimes|min:1',
            'profile_image' => 'sometimes|required|image|mimes:jpeg,png,jpg,gif|max:15000'
        ]);

        if ($request->hasFile('profile_image')) {
            $fields['profile_image'] = $request->file('profile_image')->store('profiles', 'public');
        }

        $user = User::create($fields);

        
        $verificationCode = mt_rand(1000, 9999);
        $user->code = $verificationCode;
        $user->save();

        
        $message = "Mr. $user->first_name, your verification code is: $verificationCode";
        $phoneNumber = '+963' . substr(preg_replace('/[^0-9+]/', '', $user->phone_number), 1);
        $this->ultraMsgService->sendMessage($phoneNumber, $message);

        
        $token = $user->createToken($request->first_name)->plainTextToken;

        return response([
            'message' => 'SignUp done',
            'token' => $token
        ], 200);
    } catch (\Exception $e) {
        Log::error('Registration failed: ' . $e->getMessage());
        return response([
            'error' => 'Registration failed',
            'message' => $e->getMessage()
        ], 403);
    }
}
public function resetPassword(Request $request)
{
    try {
        
        $fields = $request->validate([
            'new_password' => 'required|min:8|confirmed',
        ]);
        $user_id = Auth::id();
        $user  = User::where('id',$user_id)->first();
        
        if (!$user) {
            return response([
                'error' => 'User not found.',
            ], 404);
        }
        $verification=$user->code;
        

        if (!$verification) {
            return response([
                'error' => 'Invalid or expired verification code.',
            ], 400);
        }

        $user->password = bcrypt($fields['new_password']);
        $user->save();

        $verification->update(['used' => true]);

        return response([
            'message' => 'Password reset successfully.',
        ], 200);
    } catch (\Exception $e) {
        return response([
            'error' => 'Failed to reset password.',
            'message' => $e->getMessage(),
        ], 500);
    }
}

//     public function verifyCode(Request $request)
// {
//     try {

        
//         $fields = $request->validate([
//           //  'phone_number' => 'required|numeric|regex:/^09\d{8}$/',
//             'code' => 'required'
//         ]);
//         $user_id = Auth::id();
//            //  dd($user_id);
//         $user = User::where('id', $user_id)->first();
        
//      //  dd($user);
//         if (!$user) {
//             return response([
//                 'error' => 'User not authenticated.'
//             ], 403);
//         }
// //dd($user->phone_number);
//         // $verification = VerificationCode::where('phone_number', $user->phone_number)
//         //     ->where('code', $fields['code'])
//         //     ->where('used', false)
//         //     ->first();

//         if (!$user ->code==$fields['code']) {
//             return response([
//                 'error' => 'Invalid or expired verification code.'
//             ], 400);
//         }

        
//         // $verification->update(['used' => true]);

        
//       //  $user = User::where('phone_number', $fields['phone_number'])->first();

//         // $token = $user->createToken($user->first_name);
        
//         return response([
//             'message' => 'Phone number verified successfully.',
//             'is_valid'  => true,
//             // 'token' => $token->plainTextToken
//         ], 200);
//     } catch (\Exception $e) {
//         return response([
//             'error' => 'Verification failed',
//             'message' => $e->getMessage()
//         ], 403);
//     }
// }
public function verifyCode(Request $request)
{
    try {
        $fields = $request->validate([
            'code' => 'required'
        ]);

        $user_id = Auth::id();
        $user  = User::where('id',$user_id)->first();
        if (!$user) {
            return response([
                'error' => 'User not authenticated.'
            ], 403);
        }

        if ($user->code != $fields['code']) {
            return response([
                'error' => 'Invalid or expired verification code.'
            ], 400);
        }

       
        return response([
            'message' => 'Phone number verified successfully.',
            'is_valid' => true
        ], 200);
    } catch (\Exception $e) {
        Log::error('Verification failed: ' . $e->getMessage());
        return response([
            'error' => 'Verification failed',
            'message' => $e->getMessage()
        ], 403);
    }
}
public function resendCode(Request $request)
{
    try {
        $user_id = Auth::id();  
        $user = User::where('id',$user_id)->first();

        if (!$user) {
            return response([
                'error' => 'User not authenticated.'
            ], 403);
        }

        
        $verificationCode = mt_rand(1000, 9999);

        
        $user->code = $verificationCode;
        //dd($user);

        $user->update([
            'code' => $verificationCode,
        ]);
        

        
        $message = "Dear $user->first_name, your verification code is: $verificationCode";
        $phoneNumber = '+963' . preg_replace('/[^0-9+]/', '', $user->phone_number) ;
        $this->ultraMsgService->sendMessage($phoneNumber, $message);

        return response([
            // 'message' => 'Verification code resent successfully.',
            'code' => $verificationCode
        ], 200);

    } catch (\Exception $e) {
        
        Log::error('Failed to resend verification code: ' . $e->getMessage());
        return response([
            'error' => 'Failed to resend verification code',
            'message' => $e->getMessage()
        ], 500);
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
}
