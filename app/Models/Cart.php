<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'product_id',
        'quantity',
        'user_id',
    ];
    public function product()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
