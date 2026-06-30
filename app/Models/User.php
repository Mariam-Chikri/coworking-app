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

    // =========================================================
    // ✅ MÉTHODE ADMIN UNIQUE (CORRIGÉE)
    // =========================================================

    /**
     * Vérifie si l'utilisateur est un administrateur.
     */
    public function isAdmin(): bool
    {
        // ✅ Vérifier si la colonne 'is_admin' existe et est à true
        if (isset($this->is_admin)) {
            return (bool) $this->is_admin;
        }
        
        // ✅ Fallback: vérifier si la colonne 'role' existe (si vous l'ajoutez plus tard)
        if (isset($this->role)) {
            return $this->role === 'admin';
        }
        
        // ✅ Fallback ultime: l'utilisateur avec ID 1 est admin
        return $this->id === 1;
    }

    // =========================================================
    // Relations
    // =========================================================

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
}