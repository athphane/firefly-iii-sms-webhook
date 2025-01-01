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
    ];

    protected function casts(): array
    {
        return [
            'transaction_at' => 'datetime',
            'created_on_firefly' => 'boolean',
        ];
    }

    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
