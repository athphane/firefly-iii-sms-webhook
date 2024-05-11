<?php

namespace App\Support\Sanctum\Models;

use App\Models\PersonalAccessTokenRegex;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalAccessToken extends \Laravel\Sanctum\PersonalAccessToken
{
    public function regexes(): HasMany
    {
        return $this->hasMany(PersonalAccessTokenRegex::class);
    }
}
