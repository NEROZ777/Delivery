<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreRating extends Model
{
    use HasFactory;

    protected $fillable = ['store_id', 'user_id', 'rating'];

    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
