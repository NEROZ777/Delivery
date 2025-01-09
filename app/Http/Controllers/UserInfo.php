<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\File;

class UserInfo extends Controller implements HasMiddleware
{
    public static function middleware() {
        return [
            new Middleware('auth:sanctum', except: [])
        ];
    }

    // This function to accept the uploaded image by the user and store it within the project directory
    public function uploadImage(Request $request) {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096'
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
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Photo uploaded and stored successfully!',
                'photo_path' => $user->photo,
            ], 200);
        }


        return response()->json([
            'success' => false,
            'message' => 'Failed to upload photo.',
        ], 400);
    }

    public function registerComp(Request $request) {
        try {
            $fields = $request->validate([
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:4096',
                'gendre' => 'required|in:male,female',
                'location' => 'required',
                'birth_date' => 'required'
            ]);

            if($request->hasFile('profile_image')){ 

                $path = public_path('users_profile_images');
                if(!File::exists($path)){
                    File::makeDirectory($path, 0755, true);
                }
    
                $imageName = time() . '_' . $request->file('profile_image')->getClientOriginalName();
    
                $request->file('profile_image')->move($path, $imageName); 
    
                $user = $request->user();
                $user->profile_image = 'users_profile_images/' . $imageName;
                $user->gendre = $fields['gendre'];
                $user->location = $fields['location'];
                $user->birth_date = $fields['birth_date'];
                $user->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'information added correctly',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 403);
        }
    }
}
