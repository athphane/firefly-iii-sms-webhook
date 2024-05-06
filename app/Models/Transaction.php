<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'message',
        'vendor',
        'currency',
        'amount',
        'reference_no',
        'approval_code',
        'transaction_at',
        'vendor_id',
    ];

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    protected function casts()
    {
        return [
            'transaction_at' => 'datetime',
        ];
    }
}
