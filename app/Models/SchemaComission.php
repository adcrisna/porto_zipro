<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class SchemaComission extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "schema_comission";

    protected $casts = [
        'comission' => 'array',
        'or_comission' => 'array'
    ];
}
