<?php

namespace App\Models;

use App\Support\FireflyIII\Facades\FireflyIII;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'aliases',
    ];

    protected $casts = [
        'aliases' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        parent::updated(function ($model) {
            $model->syncVendor();
        });
    }

    public function syncVendor(): void
    {
        $notes = str('')
            ->prepend("*START:ALIASES* \n")
            ->append(implode("\n", Arr::flatten($this->aliases)))
            ->append("\n*END:ALIASES*\n");

        FireflyIII::updateAccount($this->firefly_account_id, [
            'name'  => $this->name,
            'notes' => $notes->toString(),
        ]);
    }

    public function fireflyApiUrl(): Attribute
    {
        return Attribute::get(function () {
            return __(':firefly_instance_url/api/v1/accounts/:account_id', [
                'firefly_instance_url' => config('firefly.instance.url'),
                'account_id'           => $this->firefly_account_id,
            ]);
        });
    }

    public function scopeWithAliases(Builder $query, string $searchTerm): Builder
    {
        return $query->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($searchTerm) . '%'])
            ->orWhereRaw('JSON_SEARCH(LOWER(aliases), "one", LOWER(?)) IS NOT NULL', [$searchTerm]);
    }

    public function aliasesCount(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->aliases) {
                return 0;
            }

            return count($this->aliases);
        });
    }
}
