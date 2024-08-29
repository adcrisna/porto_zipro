<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdiraTransaction extends Model
{
    use HasFactory;
    protected $table = "adira_transactions";
    protected $hidden = ['log_api'];
    protected $casts = [
        'adira_response' => 'array',
        'polling_response' => 'array',
        'post_finish' => 'array',
        'document_policy' => 'array',
        'cover_note' => 'array',
        'log_api' => 'array',
        'resubmit_response' => 'array',
        'postmikro_response' => 'array'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, "order_id");
    }
}
