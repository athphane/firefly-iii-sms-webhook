<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
