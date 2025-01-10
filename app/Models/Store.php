<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Relationship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Store extends Model
{   protected $table = 'stores'; 
    use HasFactory, Notifiable, HasApiTokens;
  
    protected $fillable = [
        'store_name',
        'store_type',
        'store_image',
        'store_rate',
        'likes',
        'location',
        'cuisine',
        'dishes'

    ];

    public function product(){
        return $this->hasMany(Product::class);
    }

    public function favourite() {
        return $this->belongsTo(Cart::class);
    }
    public function ratings()
    {
        return $this->hasMany(StoreRating::class);
    }
    
    public function averageRating()
    {
        return $this->ratings()->avg('rating');
    }
    

}
