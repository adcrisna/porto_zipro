<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cart extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "cart";

    protected $casts = [
        "data" => "array",
        "pg_callback" => "array",
        "pdf_link" => "array"
    ];

    protected $dates = ["deleted_at"];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all of the orders for the Cart
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function transaction()
    {
        return $this->hasMany(Transaction::class, 'cart_id', 'id');
    }
    public function orders()
    {
        return $this->hasMany(Order::class, 'cart_id', 'id');
    }
}
