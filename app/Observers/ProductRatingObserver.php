<?php

namespace App\Observers;

use App\Models\ProductRating;

class ProductRatingObserver
{
    /**
     * Handle the ProductRating "created" event.
     */
    public function created(ProductRating $productRating): void
    {
        $this->updateProductRating($productRating);
    }

    /**
     * Handle the ProductRating "updated" event.
     */
    public function updated(ProductRating $productRating): void
    {
        $this->updateProductRating($productRating);
    }

    /**
     * Handle the ProductRating "deleted" event.
     */
    public function deleted(ProductRating $productRating): void
    {
        $this->updateProductRating($productRating);
    }

   
    protected function updateProductRating(ProductRating $productRating): void
    {
        
        $product = $productRating->product;
    
        if ($product) {
            $averageRating = $product->ratings()->avg('rating');
    
            if ($averageRating !== null) {
                
                $product->average_rating = $averageRating;
                $product->save();
            } else {
                $product->average_rating = null;
                $product->save();
            }
        }
    }
    

    /**
     * Handle the ProductRating "restored" event.
     */
    public function restored(ProductRating $productRating): void
    {
        $this->updateProductRating($productRating);
    }

    /**
     * Handle the ProductRating "force deleted" event.
     */
    public function forceDeleted(ProductRating $productRating): void
    {
        $this->updateProductRating($productRating);
    }
}
