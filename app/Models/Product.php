<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = "product";
    protected $casts = [
        'comission' => 'array',
        'or_comission' => 'array',
        'form_limit' => 'array',
        'validation' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function schemaComission()
    {
        return $this->belongsTo(SchemaComission::class, 'schema_id', 'id');
    }

    public function schemaRefComission()
    {
        return $this->belongsTo(SchemaRefComission::class, 'schema_ref_id', 'id');
    }
}
