<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SchemaRefComission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "schema_referral";
    protected $casts = [
        'comission' => 'array',
    ];
}
