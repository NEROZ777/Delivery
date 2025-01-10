<?php

namespace App\Providers;

use App\Models\ProductRating;
use App\Models\StoreRating;
use App\Observers\ProductRatingObserver;
use App\Observers\StoreRatingObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        StoreRating::observe(StoreRatingObserver::class);

        ProductRating::observe(ProductRatingObserver::class);
    }
}
