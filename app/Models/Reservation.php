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
        'debut'               => 'datetime',
        'fin'                 => 'datetime',
        'fin_initiale'        => 'datetime',
        'liberation_anticipee' => 'datetime',
        'prix_total'          => 'decimal:2',
        'prix_prolongation'   => 'decimal:2',
        'notif_envoyee'       => 'boolean',
    ];

    // =========================================================
    // Boot
    // =========================================================

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

    // =========================================================
    // Relations
    // =========================================================

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

    // =========================================================
    // Accesseurs
    // =========================================================

    public function getDureeHeuresAttribute(): float
    {
        return round($this->debut->diffInMinutes($this->fin) / 60, 2);
    }

    // =========================================================
    // Méthodes métier
    // =========================================================

    /**
     * La réservation est-elle en cours ?
     * ► Inclut OBLIGATOIREMENT le statut "prolongee" :
     *   une réservation prolongée est toujours en cours jusqu'à sa nouvelle fin.
     */
    public function isEnCours(): bool
    {
        return in_array($this->statut, ['confirmee', 'prolongee'])
            && now()->between($this->debut, $this->fin);
    }

    /**
     * Peut-on prolonger ? Oui si en cours, non libéré anticipativement,
     * et si l'espace est disponible après la fin actuelle.
     */
    public function canBeProlonged(): bool
    {
        return $this->isEnCours() && !$this->liberation_anticipee;
    }

    /**
     * Peut-on libérer anticipativement ? Oui si en cours et pas encore terminé.
     */
    public function canBeReleasedEarly(): bool
    {
        return $this->isEnCours() && now()->lt($this->fin);
    }

    /**
     * Prolonger la réservation de $heures heures.
     * Vérifie qu'aucune autre réservation ne chevauche la nouvelle fin.
     *
     * @throws \Exception si l'espace est déjà réservé pendant la prolongation
     */
    public function prolonger(int $heures): void
    {
        if ($heures < 1 || $heures > 24) {
            throw new \Exception('Durée de prolongation invalide (1-24h).');
        }

        $nouvelleFin      = $this->fin->copy()->addHours($heures);
        $prixProlongation = $heures * $this->espace->prix_heure;

        // Vérifier qu'aucune réservation non-annulée de cet espace
        // ne chevauche le créneau [fin_actuelle, nouvelle_fin]
        $conflit = Reservation::where('espace_id', $this->espace_id)
            ->where('id', '!=', $this->id)
            ->whereNotIn('statut', ['annulee'])
            ->where('debut', '<', $nouvelleFin)
            ->where('fin', '>', $this->fin)
            ->exists();

        if ($conflit) {
            throw new \Exception(__('messages.prolongation_conflit'));
        }

        // Sauvegarder la fin initiale avant la première prolongation
        if (!$this->fin_initiale) {
            $this->fin_initiale = $this->fin->copy();
        }

        $this->fin              = $nouvelleFin;
        $this->prix_prolongation = (float)$this->prix_prolongation + $prixProlongation;
        $this->prix_total        = (float)$this->prix_total + $prixProlongation;
        $this->statut            = 'prolongee';
        $this->save();

        // Recalculer la facture associée
        if ($this->facture) {
            $this->facture->recalculer();
        }
    }

    /**
     * Libérer l'espace avant la fin prévue.
     * Recalcule le prix sur la durée réellement utilisée.
     */
    public function libererAnticipement(): void
    {
        $maintenant             = now();
        $this->liberation_anticipee = $maintenant;
        $this->fin              = $maintenant;
        $this->statut           = 'terminee';

        // Recalcul au prorata des heures réellement occupées (minimum 1h)
        $heuresReelles = max(1, $this->debut->diffInHours($maintenant));
        $this->prix_total = round($heuresReelles * $this->espace->prix_heure, 2);
        $this->save();

        if ($this->facture) {
            $this->facture->recalculer();
        }
    }

    // =========================================================
    // Scopes
    // =========================================================

    /** Réservations en cours (confirmee ou prolongee). */
    public function scopeEnCours($query)
    {
        return $query->whereIn('statut', ['confirmee', 'prolongee'])
            ->where('debut', '<=', now())
            ->where('fin', '>=', now());
    }

    /** Réservations à venir. */
    public function scopeAVenir($query)
    {
        return $query->whereIn('statut', ['en_attente', 'confirmee', 'prolongee'])
            ->where('debut', '>', now());
    }

    // =========================================================
    // Helpers statiques
    // =========================================================

    /**
     * Vérifie si un espace est disponible sur un créneau,
     * en excluant optionnellement une réservation existante.
     */
    public static function espaceDisponible(
        int    $espaceId,
        string $debut,
        string $fin,
        ?int   $excludeId = null
    ): bool {
        $q = static::where('espace_id', $espaceId)
            ->whereNotIn('statut', ['annulee'])
            ->where(fn($q) =>
                $q->whereBetween('debut', [$debut, $fin])
                  ->orWhereBetween('fin', [$debut, $fin])
                  ->orWhere(fn($q2) =>
                      $q2->where('debut', '<=', $debut)->where('fin', '>=', $fin)
                  )
            );

        if ($excludeId) {
            $q->where('id', '!=', $excludeId);
        }

        return !$q->exists();
    }

    /**
     * Vérifie si un utilisateur est disponible (pas de réservation simultanée).
     */
    public static function utilisateurDisponible(
        int    $userId,
        string $debut,
        string $fin,
        ?int   $excludeId = null
    ): bool {
        $q = static::where('user_id', $userId)
            ->whereNotIn('statut', ['annulee'])
            ->where(fn($q) =>
                $q->whereBetween('debut', [$debut, $fin])
                  ->orWhereBetween('fin', [$debut, $fin])
                  ->orWhere(fn($q2) =>
                      $q2->where('debut', '<=', $debut)->where('fin', '>=', $fin)
                  )
            );

        if ($excludeId) {
            $q->where('id', '!=', $excludeId);
        }

        return !$q->exists();
    }
}

