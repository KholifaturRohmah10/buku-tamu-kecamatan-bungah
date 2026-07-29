<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    public const ROLE_TAMU = 'tamu';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_VALIDATOR = 'validator';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'is_admin',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'role' => 'string',
            'is_admin' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function daftarKunjunganTamu(): HasMany
    {
        return $this->hasMany(KunjunganTamu::class, 'id_petugas');
    }

    public function daftarSurveiTamu(): HasMany
    {
        return $this->hasMany(SurveiTamu::class, 'id_pengguna');
    }

    public function hasCompletedSurveiTamu(): bool
    {
        return $this->daftarSurveiTamu()
            ->whereNotNull('waktu_dikirim')
            ->exists();
    }

    public function latestSurveiTamu(): ?SurveiTamu
    {
        return $this->daftarSurveiTamu()
            ->whereNotNull('waktu_dikirim')
            ->latest('waktu_dikirim')
            ->latest('id')
            ->first();
    }

    public function daftarKunjunganTamuDivalidasi(): HasMany
    {
        return $this->hasMany(KunjunganTamu::class, 'id_validator');
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isValidator(): bool
    {
        return $this->role === self::ROLE_VALIDATOR;
    }

    public function isGuest(): bool
    {
        return $this->role === self::ROLE_TAMU;
    }

    public function dashboardRoute(): string
    {
        return match ($this->role) {
            self::ROLE_ADMIN => 'admin.dashboard',
            self::ROLE_VALIDATOR => 'validator.dashboard',
            default => 'kunjungan-tamu.index',
        };
    }

    protected function roleLabel(): Attribute
    {
        return Attribute::get(
            fn (): string => match ($this->role) {
                self::ROLE_ADMIN => 'Admin',
                self::ROLE_VALIDATOR => 'Validator',
                default => 'Tamu',
            }
        );
    }
}
