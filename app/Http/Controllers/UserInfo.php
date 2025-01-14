<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use Illuminate\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;


use function PHPUnit\Framework\isEmpty;

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
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:15000'
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
                'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:15000',
                'gendre' => 'required|in:male,female,Engineer,Bashar AlKalb',
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

    // This function to edit profile
    public function editProfile(Request $request) {
        try {
            $fields = $request->validate([
                'first_name' => 'sometimes',
                'last_name' => 'sometimes',
                'profile_image' => 'sometimes',
                'location' => 'sometimes',
                'email' => 'sometimes|email'
            ]);

            $user = auth('sanctum')->user();

            if(!$user || !($user instanceof \App\Models\User)) {
                return response([
                    'message' => 'unauthorized'
                ], 401);
            }

            $user->fill($fields);
            
            if ($request->hasFile('profile_image')) {
                $path = public_path('users_profile_images');
                if (!File::exists($path)) {
                    File::makeDirectory($path, 0755, true);
                }
    
                $originalName = pathinfo($request->file('profile_image')->getClientOriginalName(), PATHINFO_FILENAME);
                $imageName = time() . '_' . Str::slug($originalName) . '.' . $request->file('profile_image')->getClientOriginalExtension();
    
                $request->file('profile_image')->move($path, $imageName);
    
                // Update the profile_image field with the uploaded file path
                $user->profile_image = 'users_profile_images/' . $imageName;
            }

            $user->save();

            return response([
                'message' => 'profile updated'
            ], 200);
        } catch(\Exception $e) {
            return response([
                'message' => 'cannot update',
                'error' => $e->getMessage()
            ], 403);
        }
    }

    // This function to add to the user's favourites list
    public function addToFav(Request $request) {
        try {
            $fields = $request->validate([
                'product_id' => 'required_without:store_id',
                'store_id' => 'required_without:product_id'
            ]); 

            if (isset($fields['product_id']) && isset($fields['store_id'])) {
                return response([
                    'message' => 'You can only provide either product_id or store_id, not both.'
                ], 403);
            }

            $user = auth('sanctum')->user();

            if(!$user) {
                return response([
                    'message' => 'cannot find the user'
                ], 403);
            }

            $query = Favourite::where('user_id', $user->id);

            if (isset($fields['product_id'])) {
                $query->where('product_id', $fields['product_id']);
            }
    
            if (isset($fields['store_id'])) {
                $query->where('store_id', $fields['store_id']);
            }

            $existingFav = $query->first();
            if ($existingFav) {
                $existingFav->delete();
    
                return response([
                    'message' => 'removed from favorite'
                ], 200);
            }

            $fields['user_id'] = $user->id;
            Favourite::create($fields);

            return response([
                'message' => 'added to favourite'
            ], 200);
        } catch(\Exception $e) {

        }
    }

    // This function to remove one item from favourites list
    public static function removeFav(Request $request) {
        try {
            $fields = $request->validate([
                'product_id' => 'required_without:store_id',
                'store_id' => 'required_without:product_id'
            ]);
            
            $user = auth('sanctum')->user();

            if(!$user) {
                return response([
                    'message' => 'unauthorized'
                ], 401);
            }

            $query = Favourite::where('user_id', $user->id);

            if(!empty($fields['product_id'])) {
                $query->where('product_id', $fields['product_id']);
            }

            if(!empty($fields['store_id'])) {
                $query->where('store_id', $fields['store_id']);
            }

            $fav = $query->first();

            if(!$fav) {
                return response([
                    'message' => 'cannot find the item'
                ], 403);
            }

            $fav->delete();

            return response([
                'message' => 'the item removed from the favourite list'
            ], 200);
        } catch(\Exception $e) { 
            return response([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // This function to get all favourite products
    public function getAllFavProducts() {
        try {
            $user = auth('sanctum')->user();

            if(!$user) {
                return response([
                    'message' => 'unauthorized'
                ], 401);
            }

            $favList = Favourite::where('user_id', $user->id)
                            ->join('products', 'favourites.product_id', '=', 'products.id')
                            ->join('stores', 'products.store_id', '=', 'stores.id')
                            ->select('products.id as product_id',
                             'products.name as title',
                             'products.description as description',
                             'products.price as price',
                             'products.image_url as imageUrl',
                             'products.id as id',
                             'products.ingredients as ingredients',
                             'products.average_rating as average_rating',
                             'products.store_id as store_id',
                             'products.quantity as quantity',
                             'stores.store_name as store_name')
                            ->get();

            if($favList->isEmpty()) {
                return response([
                    'message' => 'nothing found'
                ], 403);
            }

            return response([
                'favourite_products' => $favList
            ], 200);
        } catch(\Exception $e) {
            return response([
                'message' => 'an issue happend',
                'error' => $e->getMessage()
            ], 403);
        }
    }

    // This function to get all favourite stores
    public function getAllFavStores() {
        try {
            $user = auth('sanctum')->user();

            if(!$user) {
                return response([
                    'message' => 'unauthorized'
                ], 401);
            }

            $favList = Favourite::where('user_id', $user->id)
                            ->join('stores', 'favourites.store_id', '=', 'stores.id')
                            ->select('stores.id as id',
                             'stores.store_name as store_name',
                             'stores.store_type as store_type',
                             'stores.cuisine as cuisine',
                             'stores.likes as likes',
                             'stores.location as location',
                             'stores.dishes as dishes',
                             'stores.store_image as store_image',
                             'stores.store_rate as store_rate',
                             'stores.created_at as created_at',
                             'stores.updated_at as updated_at')
                            ->get();

            if($favList->isEmpty()) {
                return response([
                    'message' => 'nothing found'
                ], 403);
            }

            return response([
                'favourite_stores' => $favList
            ], 200);
        } catch(\Exception $e) {
            return response([
                'message' => 'an issue happend',
                'error' => $e->getMessage()
            ], 403);
        }
    }

    // This function to get user info
    public function userInfo() {
        try {
            $user = auth('sanctum')->user();
    
            if(!$user) {
                return response([
                    'message' => 'unauthorized'
                ], 401);
            }

            return response([
                'user_info' => $user
            ], 200);
        } catch(\Exception $e) {

        }
        
    }
}
