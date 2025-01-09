<?php

namespace App\Http\Controllers;

use App\Models\ProductRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ProductRatingController1 extends Controller
{

   
public function store(Request $request)
{
    
    $request->validate([
        'product_id' => 'required|exists:products,id',  
        'rating' => 'required|integer|between:1,5',  
    ]);
    // if (Auth::check()) {
    //    $userId= Auth::id();
    // } else {
    //     return 'No user is logged in';
    // }

    $existingRating = ProductRating::where('user_id',Auth::id())
                                   ->where('product_id', $request->input('product_id'))
                                   ->first();
                                

    if ($existingRating) {
        
        $existingRating->update([
            'rating' => $request->input('rating')
        ]);

        return response()->json(['message' => 'Rating updated successfully.']);
    } else {
        
        $rating = ProductRating::create([
            'user_id' => Auth::id(),
            'product_id' => $request->input('product_id'),
            'rating' => $request->input('rating'),
        ]);
        
        return response()->json(['message' => 'Rating added successfully.',
        
    
    
    ]);
   
    
    }
}
    public function destroy(Request $request){

        $request ->validate([

            'product_id'=> 'required|exists:products,id',

        ]);
        $rating = ProductRating::where('user_id', Auth::id())
        ->where('product_id', $request->input('product_id'))
        ->first();
        if ($rating) {
            
            $rating->delete();
            return response()->json(['message' => 'Rating deleted successfully.']);
        } else {
            // إذا لم يتم العثور على التقييم
            return response()->json(['message' => 'Rating not found.'], 404);
        }
    }

}
