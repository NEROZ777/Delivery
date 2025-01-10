<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Favourite extends Model
{

    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'user_id',
        'product_id',
        'store_id'
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function product() {
        return $this->hasOne(Product::class);
    }

    public function store() {
        return $this->hasOne(Store::class);
    }
}
