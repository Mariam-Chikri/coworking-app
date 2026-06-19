<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'locale', 'is_admin',
        'telephone', 'entreprise', 'avatar',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function favoris()
    {
        return $this->hasMany(Favori::class);
    }

    public function espacesFavoris()
    {
        return $this->belongsToMany(Espace::class, 'favoris');
    }

    public function avis()
    {
        return $this->hasMany(Avis::class);
    }

    public function factures()
    {
        return $this->hasMany(Facture::class);
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }
}
