<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Product extends Model
{
<<<<<<< HEAD
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'description',
        'price',
        'quantity',
        'store_id'
    ];

    public function store() {
        return $this->belongsTo('App/Model/Store');
    }
}
=======
    
        // use HasFactory;
        protected $fillable = [
            'name',        
            'description', 
            'price',       
            'quantity',     
            'image',       
        ];
    
       
        public function cartItems()
        {
            return $this->belongsToMany(Cart::class)->withPivot('quantity');
        }
    }
    

>>>>>>> 29879969736225a0a703cef765ec88530dbcfaeb
