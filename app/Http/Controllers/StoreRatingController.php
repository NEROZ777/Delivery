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
            'stores_id' => 'required|exists:stores,id',  
            'rating' => 'required|integer|between:0,5',  
        ]);
    
    
        $existingRating = StoreRating::where('user_id', Auth::id())
                                       ->where('stores_id', $request->input('stores_id'))
                                       ->first();
    
        if ($existingRating) {
            
            $existingRating->update([
                'rating' => $request->input('rating')
            ]);
    
            return response()->json(['message' => 'Rating updated successfully.']);
        } else {
            // إذا لم يكن التقييم موجودًا، نقوم بإضافته
            $rating = StoreRating::create([
                'user_id' => Auth::id(),
                'stores_id' => $request->input('stores_id'),
                'rating' => $request->input('rating'),
            ]);
    
            return response()->json(['message' => 'Rating added successfully.']);
        }
    }
    
}
