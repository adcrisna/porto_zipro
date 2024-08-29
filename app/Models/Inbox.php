<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inbox extends Model
{
    use HasFactory;
    protected $table = "inbox";

    protected $fillable = [
        "from_user_id",
        "to_user_id",
        "message"
    ];
}
