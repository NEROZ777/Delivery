<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;


use function Laravel\Prompts\password;

class AuthController extends Controller
{
    // This function for user registering 
    public function register(Request $request) { 
        try {
            $fields = $request->validate([
                'first_name' => 'required|min:2|max:255',
                'last_name' => 'required|min:2|max:255',
                'email' => 'required|email|unique:users',
                'phone_number' => 'required|numeric|regex:/^09\d{8}$/|unique:users',
                'password' => 'required|min:6|confirmed'
            ]);
    
            $user = User::create($fields);
    
            $token = $user->createToken($request->first_name);
    
            return response([
                'message' => 'SignUp done',
                'token' => $token->plainTextToken
            ], 200);
        } catch(\Exception $e){
            return response([
                'error' => 'Registration field',
                'message' => $e->getMessage()
            ], 403);
        }
    }
    
    // This function let user enter his account if he inputs validate phone number and password
    public function login(Request $request) { 
        try {
            $fields = $request->validate([
                'phone_number' => 'required',
                'password' => 'required'
            ]);
    
            $user = User::where('phone_number', $request->phone_number)->first();
    
            if(!$user || !Hash::check($request->password, $user->password)) {
                return response([
                    'error' => 'login error',
                    'message' => 'the provided credintials are incorrect.'
                ], 403);
            }
    
            $token = $user->createToken($user->first_name);
    
            return response([
                'message' => 'Login done',
                'token' => $token->plainTextToken
            ], 200);
        } catch(\Exception $e){
            return response([
                'error' => 'login error',
                'message' => $e->getMessage()
            ], 403);
        }
    }

    // This function to logout from the account
    public function logout(Request $request) {
        try {
            $request->user()->tokens()->delete();
    
            return response([
                'message' => 'you are logged out.'
            ], 200);
        } catch(\Exception $e){
            return response([
                'error' => 'logout error',
                'message' => $e->getMessage()
            ],403);
        }
    }

    public function registerComp(Request $request) {
        try {
            $fields = $request->validate([
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
                'gendre' => 'required|in:male,female',
                'location' => 'required',
                'birth_date' => 'required'
            ]);

            if($request->hasFile('image')){ 

                $path = public_path('users_profile_images');
                if(!File::exists($path)){
                    File::makeDirectory($path, 0755, true);
                }
    
                $imageName = time() . '_' . $request->file('image')->getClientOriginalName();
    
                $request->file('image')->move($path, $imageName); 
    
                $user = $request->user();
                $user->profile_image = 'users_profile_images/' . $imageName;
                $user->gendre = $fields['gendre'];
                $user->location = $fields['location'];
                $user->birth_date = $fields['birth_date'];
                $user->save();
    
                return response()->json([
                    'success' => true,
                    'message' => 'Photo uploaded and stored successfully!',
                    'photo_path' => $user->photo,
                ], 200);
            }
        } catch (\Exception $e) {

        }
    }
}
