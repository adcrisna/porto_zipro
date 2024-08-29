<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $dates = ['deleted_at'];
    protected $casts = [
        "data" => "array",
        "additional_data" => "array",
        'validation' => 'array',
    ];

    /**
     * Get the cart that owns the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'id');
    }

    /**
     * Get the adira_trx that owns the Order
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function adira_trx()
    {
        return $this->belongsTo(AdiraTransaction::class, 'id', 'order_id');
    }

    public function inquiry()
    {
        return $this->belongsTo(InquiryMv::class, 'id', 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function transaction()
    {
        return $this->HasOne(Transaction::class, 'id','transaction_id');
    }
}
