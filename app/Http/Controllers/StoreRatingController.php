<?php

namespace App\Http\Controllers;

use App\Models\StoreRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreRatingController extends Controller
{
    public function storeing(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',  
            'rating' => 'required|integer|between:0,5',  
        ]);
    
        $existingRating = StoreRating::where('user_id', Auth::id())
                                     ->where('store_id', $request->input('store_id'))
                                     ->first();
    
        if ($existingRating) {
            // تحديث التقييم الموجود
            $existingRating->update([
                'rating' => $request->input('rating')
            ]);
    
            return response()->json(['message' => 'Rating updated successfully.']);
        } else {
            
            $rating = StoreRating::create([
                'user_id' => Auth::id(),
                'store_id' => $request->input('store_id'),
                'rating' => $request->input('rating'),
            ]);
    
            return response()->json(['message' => 'Rating added successfully.']);
        }
    }
    public function destroy(Request $request){

        $request ->validate([

            'store_id'=> 'required|exists:stores,id',

        ]);
        $rating = StoreRating::where('user_id', Auth::id())
        ->where('store_id', $request->input('store_id'))
        ->first();
        if ($rating) {
            
            $rating->delete();
            return response()->json(['message' => 'Rating deleted successfully.']);
        } else {
            
            return response()->json(['message' => 'Rating not found.'], 404);
        }
    }
    
}
