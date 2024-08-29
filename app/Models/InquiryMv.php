<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InquiryMv extends Model
{
    use HasFactory;
    protected $table = "inquiry_mv";

    protected $casts = [
        "item" => "array",
        "data" => "array"
    ];

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id');
    }
}
