<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'espace_id', 'debut', 'fin', 'fin_initiale',
        'liberation_anticipee', 'statut', 'prix_total', 'prix_prolongation',
        'notes', 'numero', 'nombre_personnes', 'notif_envoyee',
    ];

    protected $casts = [
        'debut' => 'datetime',
        'fin' => 'datetime',
        'fin_initiale' => 'datetime',
        'liberation_anticipee' => 'datetime',
        'prix_total' => 'decimal:2',
        'prix_prolongation' => 'decimal:2',
        'notif_envoyee' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($reservation) {
            if (!$reservation->numero) {
                $count = static::withTrashed()->count() + 1;
                $reservation->numero = 'REZ-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function espace()
    {
        return $this->belongsTo(Espace::class);
    }

    public function facture()
    {
        return $this->hasOne(Facture::class);
    }

    public function avis()
    {
        return $this->hasOne(Avis::class);
    }

    public function getDureeHeuresAttribute(): float
    {
        return round($this->debut->diffInMinutes($this->fin) / 60, 2);
    }

    public function isEnCours(): bool
    {
        return $this->statut === 'confirmee'
            && now()->between($this->debut, $this->fin);
    }

    public function canBeProlonged(): bool
    {
        return $this->isEnCours() && !$this->liberation_anticipee;
    }

    public function canBeReleasedEarly(): bool
    {
        return $this->isEnCours() && now()->lt($this->fin);
    }

    public function prolonger(int $heures): void
    {
        $nouvelleFin = $this->fin->addHours($heures);
        $prixProlongation = $heures * $this->espace->prix_heure;

        // Vérifier disponibilité
        $conflit = Reservation::where('espace_id', $this->espace_id)
            ->where('id', '!=', $this->id)
            ->whereNotIn('statut', ['annulee'])
            ->where('debut', '<', $nouvelleFin)
            ->where('fin', '>', $this->fin)
            ->exists();

        if ($conflit) {
            throw new \Exception(__('messages.prolongation_conflit'));
        }

        if (!$this->fin_initiale) {
            $this->fin_initiale = $this->fin;
        }

        $this->fin = $nouvelleFin;
        $this->prix_prolongation += $prixProlongation;
        $this->prix_total += $prixProlongation;
        $this->statut = 'prolongee';
        $this->save();

        // Mettre à jour la facture
        if ($this->facture) {
            $this->facture->recalculer();
        }
    }

    public function libererAnticipement(): void
    {
        $this->liberation_anticipee = now();
        $this->fin = now();
        $this->statut = 'terminee';

        // Recalculer le prix
        $dureeReelle = $this->debut->diffInHours(now());
        $this->prix_total = $dureeReelle * $this->espace->prix_heure;
        $this->save();

        if ($this->facture) {
            $this->facture->recalculer();
        }
    }

    public function scopeEnCours($query)
    {
        return $query->where('statut', 'confirmee')
            ->where('debut', '<=', now())
            ->where('fin', '>=', now());
    }

    public function scopeAVenir($query)
    {
        return $query->whereIn('statut', ['en_attente', 'confirmee'])
            ->where('debut', '>', now());
    }
}
