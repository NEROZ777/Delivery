<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Factories\Relationship;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Store extends Model
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'store_name',
        'store_type',
        'store_image',
        'store_rate',
        'about'
    ];

    public function product(){
        return $this->hasMany('App/Model/Product');
    }
}
