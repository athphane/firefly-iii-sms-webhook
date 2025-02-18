<?php

namespace App\Models;

use App\Jobs\ProcessTransactionJob;
use App\Support\FireflyIII\Enums\Currencies;
use App\Support\FireflyIII\Facades\FireflyIII;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Transaction extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

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
        if ($this->firefly_transaction_id) {
            return;
        }

        ProcessTransactionJob::dispatch($this);
    }

    public function fireflyUrl(): Attribute
    {
        return Attribute::get(function () {
            return FireflyIII::getTransactionUrl($this->firefly_transaction_id);
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('receipt')
            ->singleFile();
    }

    public function receiptPath(): Attribute
    {
        return Attribute::get(function () {
            return $this->getFirstMediaPath('receipt');
        });
    }
}
