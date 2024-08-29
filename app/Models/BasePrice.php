<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BasePrice extends Model
{
    use HasFactory;
    protected $table = "base_price";

    protected $casts = [
        'tahun' => 'array',
        'price' => 'array',
    ];
}
