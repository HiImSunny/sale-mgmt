<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * @var mixed|string
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'provider',
        'provider_id',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function assignRole($role)
    {
        return $this->role = $role;
    }

    public function hasProvider($provider): bool
    {
        return $this->provider === $provider && $this->provider_id;
    }

    public function isSocialUser(): bool
    {
        return !is_null($this->provider) && !is_null($this->provider_id);
    }
}
