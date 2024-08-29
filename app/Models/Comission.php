<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comission extends Model
{
    use HasFactory;

    protected $table = "comission";

    protected $casts = [
        "formula" => "array"
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'trx_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

?>