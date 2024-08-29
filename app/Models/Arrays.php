<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arrays extends Model
{
    use HasFactory;
    protected $table = "array";
    protected $casts = [
        "value" => "array"
    ];
}
