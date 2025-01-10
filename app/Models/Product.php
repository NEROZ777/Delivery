<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Product extends Model
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'store_id',
        'ingredients',
        'image_url'
    ];

    public function store() {
        return $this->belongsTo(Store::class);
    }
<<<<<<< HEAD

    public function favourite() {
        return $this->belongsTo(Favourite::class);
    }

    public function cart() {
        return $this->belongsTo(Cart::class);
    }
=======
    public function ratings()
{
    return $this->hasMany(ProductRating::class);
}

public function averageRating()
{
    return $this->ratings()->avg('rating');
}

>>>>>>> 61e584144770b90f440b788db56c9f2ffb2df898
}
