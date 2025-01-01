<?php

namespace App\Models;

use App\Jobs\ProcessTransactionJob;
use App\Support\FireflyIII\Enums\Currencies;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'card',
        'transaction_at',
        'currency',
        'amount',
        'location',
        'approval_code',
        'reference_no',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'transaction_at' => 'datetime',
            'currency'       => Currencies::class,
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function process(): void
    {
        ProcessTransactionJob::dispatch($this);
    }
}
