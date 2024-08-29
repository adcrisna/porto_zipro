<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Renewal extends Model
{
    use HasFactory;
    protected $table = "renewal";
    protected $casts = [
        "data" => "array"
    ];

    public function order()
    {
        return $this->belongsTo(Orders::class);
    }

}
