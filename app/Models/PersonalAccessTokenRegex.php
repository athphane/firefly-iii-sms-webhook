<?php

namespace App\Models;

use App\Support\Sanctum\Models\PersonalAccessToken;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalAccessTokenRegex extends Model
{
    use HasFactory;

    public function personalAccessToken(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PersonalAccessToken::class);
    }
}
