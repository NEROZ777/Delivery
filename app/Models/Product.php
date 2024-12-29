<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    
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
    

