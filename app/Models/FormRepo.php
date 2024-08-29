<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormRepo extends Model
{
    use HasFactory;
    protected $table = "form_repo";

    protected $casts = [
        "lang" => "array"
    ];
}
