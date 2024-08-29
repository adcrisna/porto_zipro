<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReferral extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "referral";
    protected $casts = [
        "data" => "array"
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function picked()
    {
        return $this->belongsTo(User::class, 'picked_user_id');
    }
}
