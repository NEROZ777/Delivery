<?php

namespace App\Http\Controllers;

use App\Models\Favourite;
use Illuminate\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\File;

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
            $fields['user_id'] = $user->id;
            $fav = Favourite::create($fields);

            return response([
                'message' => 'added to favourite'
            ], 200);
        } catch(\Exception $e) {

        }
    }

    // This function to remove one item from favourites list
    public function removeFav(Request $request) {
        try {
            $fields = $request->validate([
                'id' => 'required|integer|exists:favourites,id'
            ]);
            
            $user = auth('sanctum')->user();

            if(!$user) {
                return response([
                    'message' => 'unauthorized'
                ], 401);
            }

            $fav = Favourite::where('id', $fields['id'])
                            ->where('user_id', $user->id)
                            ->first();

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
                            ->select('products.id as product_id',
                             'products.name as product_name',
                             'products.description as description',
                             'products.quantity as quantity',
                             'products.store_id as store_id')
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
                            ->select('stores.id as store_id',
                             'stores.store_name as store_name',
                             'stores.store_type as store_type',
                             'stores.store_image as store_image',
                             'stores.store_rate as store_rate',
                             'stores.about as about')
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
