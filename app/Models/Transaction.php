<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = "transaction";
    protected $casts = [
        "trx_data" => "array",
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, "cart_id");
    }

    public function order()
    {
        return $this->belongsTo(Order::class, "order_id");
    }
}
