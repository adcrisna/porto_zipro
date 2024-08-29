<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdiraProvince extends Model
{
    use HasFactory;

    protected $table = "adira_province";
    protected $casts = [
        "cities" => "array"
    ];
}
