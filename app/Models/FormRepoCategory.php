<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormRepoCategory extends Model
{
    use HasFactory;
    protected $table = "form_repo_categories";
    protected $casts = [
        "form_json" => "array",
        "form_validation" => "array",
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
