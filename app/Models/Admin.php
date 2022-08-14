<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasFactory, SoftDeletes, HasFactory, Notifiable, HasRoles, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'password',
        'status'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected string $guard_name = 'custom';

    const STATUS_ENABLED = 'enabled';
    const STATUS_DISABLED = 'disabled';

    public function isDisabled(): bool
    {
        return $this->getAttribute('status') == self::STATUS_DISABLED;
    }

    /**
     * @param string $name
     * @param array|string[] $abilities
     * @param null $merchantId
     * @return NewAccessToken
     */
    public function createToken(string $name, array $abilities = ['*'], $merchantId = null): NewAccessToken
    {
        $token = $this->tokens()->create([
            'name' => $name,
            'token' => hash('sha256', $plainTextToken = Str::random(40)),
            'abilities' => $abilities,
            'merchant_id' => $merchantId,
            'last_used_at' => now()
        ]);

        return new NewAccessToken($token, $token->getKey() . '|' . $plainTextToken);
    }

    public function updateToken($array)
    {
        $this->currentAccessToken()->update($array);
    }

    public function getPhone()
    {
        return $this->getAttribute('phone');
    }

}
