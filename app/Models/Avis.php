<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Avis extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'espace_id', 'reservation_id',
        'note', 'titre', 'commentaire', 'valide',
    ];

    protected $casts = [
        'note' => 'integer',
        'valide' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function espace()
    {
        return $this->belongsTo(Espace::class);
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function getEtoilesAttribute(): string
    {
        return str_repeat('★', $this->note) . str_repeat('☆', 5 - $this->note);
    }
}
