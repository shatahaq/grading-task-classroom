<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class OauthToken extends Model
{
    protected $fillable = [
        'user_id',
        'access_token_encrypted',
        'refresh_token_encrypted',
        'expiry_date',
        'scopes',
        'token_type',
        'last_refreshed_at',
        'revoked',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'datetime',
            'scopes' => 'array',
            'last_refreshed_at' => 'datetime',
            'revoked' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function accessToken(): string
    {
        return Crypt::decryptString($this->access_token_encrypted);
    }

    public function refreshToken(): ?string
    {
        return $this->refresh_token_encrypted
            ? Crypt::decryptString($this->refresh_token_encrypted)
            : null;
    }
}
