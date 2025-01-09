<?php

namespace App\Http\Controllers;

use App\Models\ProductRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ProductRatingController extends Controller
{

   
public function store(Request $request)
{
    // تحقق من صحة البيانات المدخلة
    $request->validate([
        'product_id' => 'required|exists:products,id',  
        'rating' => 'required|integer|between:0,5',  
    ]);


    $existingRating = ProductRating::where('user_id', Auth::id())
                                   ->where('product_id', $request->input('product_id'))
                                   ->first();

    if ($existingRating) {
        
        $existingRating->update([
            'rating' => $request->input('rating')
        ]);

        return response()->json(['message' => 'Rating updated successfully.']);
    } else {
        // إذا لم يكن التقييم موجودًا، نقوم بإضافته
        $rating = ProductRating::create([
            'user_id' => Auth::id(),
            'product_id' => $request->input('product_id'),
            'rating' => $request->input('rating'),
        ]);

        return response()->json(['message' => 'Rating added successfully.']);
    }
}

}
