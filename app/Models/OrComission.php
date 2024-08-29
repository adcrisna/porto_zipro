<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrComission extends Model
{
    use HasFactory;

    protected $table = "or_comission";
    protected $casts = [
        "formula" => "array"
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'trx_id', 'id');
    }


    public function user()
    {
        return $this->belongsTo(User::class, 'from_id', 'id');
    }

    public function userto()
    {
        return $this->belongsTo(User::class, 'to_id', 'id');
    }
}
