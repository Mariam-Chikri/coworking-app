<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Espace extends Model
{
    use HasFactory;

    protected $table = 'espaces';

    protected $fillable = [
        'nom', 'type', 'description',
        'capacite_min', 'capacite_max',
        'prix_heure', 'prix_journee', 'prix_mois',
        'photo_principale', 'equipements', 'adresse',
        'latitude', 'longitude', 'actif',
    ];

    protected $casts = [
        'equipements' => 'array',
        'actif'       => 'boolean',
    ];

    // =========================================================
    // Relations
    // =========================================================

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function avis()
    {
        return $this->hasMany(Avis::class);
    }

    // =========================================================
    // Accesseurs
    // =========================================================

    /**
     * URL de la photo principale.
     * Fallback vers une image Picsum si aucune photo n'est définie.
     */
    public function getPhotoUrlAttribute(): string
    {
        if ($this->photo_principale && \Storage::disk('public')->exists($this->photo_principale)) {
            return asset('storage/' . $this->photo_principale);
        }
        return "https://picsum.photos/seed/{$this->id}/400/300";
    }

    /**
     * Alias "capacite" → capacite_max pour compatibilité.
     * Utilisé dans ReservationForm, espaces/show, etc.
     */
    public function getCapaciteAttribute(): int
    {
        return (int) ($this->capacite_max ?? $this->capacite_min ?? 1);
    }

    /** Nom localisé (fr/en) */
    public function getNomLocalIsedAttribute(): string
    {
        if (app()->getLocale() === 'en' && !empty($this->attributes['nom_en'])) {
            return $this->attributes['nom_en'];
        }
        return $this->attributes['nom'] ?? '';
    }

    /** Description localisée (fr/en) */
    public function getDescriptionLocalIsedAttribute(): ?string
    {
        if (app()->getLocale() === 'en' && !empty($this->attributes['description_en'])) {
            return $this->attributes['description_en'];
        }
        return $this->attributes['description'] ?? null;
    }

    /** Libellé du type en français */
    public function getTypeLabelAttribute(): string
    {
        return [
            'bureau_individuel' => 'Bureau Individuel',
            'bureau_prive'      => 'Bureau Privé',
            'open_space'        => 'Open Space',
            'salle_reunion'     => 'Salle de Réunion',
            'salle_conference'  => 'Salle de Conférence',
        ][$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    /** Note moyenne des avis validés */
    public function getNotesMoyenneAttribute(): float
    {
        return (float) round($this->avis()->where('valide', true)->avg('note') ?? 0, 1);
    }

    /** Prix heure formaté */
    public function getPrixHeureFormattedAttribute(): string
    {
        return number_format($this->prix_heure, 2, ',', ' ') . ' MAD';
    }

    /** Prix journée formaté */
    public function getPrixJourneeFormattedAttribute(): ?string
    {
        return $this->prix_journee
            ? number_format($this->prix_journee, 2, ',', ' ') . ' MAD'
            : null;
    }

    /** Prix mois formaté */
    public function getPrixMoisFormattedAttribute(): ?string
    {
        return $this->prix_mois
            ? number_format($this->prix_mois, 2, ',', ' ') . ' MAD'
            : null;
    }

    /**
     * Taux d'occupation du mois courant (%).
     * Basé sur les réservations confirmées / terminées / prolongées.
     */
    public function getTauxOccupationAttribute(): float
    {
        $heuresOccupees = $this->reservations()
            ->whereIn('statut', ['confirmee', 'terminee', 'prolongee'])
            ->whereMonth('debut', now()->month)
            ->whereYear('debut', now()->year)
            ->get()
            ->sum(fn($r) => $r->debut->diffInHours($r->fin));

        $heuresDisponibles = now()->daysInMonth * 10;

        return $heuresDisponibles > 0
            ? round(($heuresOccupees / $heuresDisponibles) * 100, 1)
            : 0;
    }

    // =========================================================
    // Scopes
    // =========================================================

    public function scopeActive($query)
    {
        return $query->where('actif', true);
    }

    public function scopeReservable($query)
    {
        return $query->where('actif', true);
    }

    public function scopeNonReservable($query)
    {
        return $query->where('actif', false);
    }

    public function scopeByType($query, string $type)
    {
        return $type ? $query->where('type', $type) : $query;
    }

    public function scopeSearch($query, string $search)
    {
        return $search
            ? $query->where(fn($q) =>
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
              )
            : $query;
    }

    /**
     * Scope disponible : exclut les espaces qui ont une réservation
     * non annulée chevauchant le créneau [$debut, $fin].
     */
    public function scopeDisponible($query, string $debut, string $fin)
    {
        return $query->whereDoesntHave('reservations', function ($q) use ($debut, $fin) {
            $q->whereNotIn('statut', ['annulee'])
              ->where(fn($sub) =>
                  $sub->whereBetween('debut', [$debut, $fin])
                      ->orWhereBetween('fin', [$debut, $fin])
                      ->orWhere(fn($sub2) =>
                          $sub2->where('debut', '<=', $debut)
                               ->where('fin', '>=', $fin)
                      )
              );
        });
    }

    // =========================================================
    // Méthodes utilitaires
    // =========================================================

    /**
     * Vérifie si l'espace est disponible sur un créneau donné.
     */
    public function isAvailable(string $debut, string $fin, ?int $excludeReservationId = null): bool
    {
        $query = $this->reservations()
            ->whereNotIn('statut', ['annulee'])
            ->where(fn($q) =>
                $q->whereBetween('debut', [$debut, $fin])
                  ->orWhereBetween('fin', [$debut, $fin])
                  ->orWhere(fn($q2) =>
                      $q2->where('debut', '<=', $debut)->where('fin', '>=', $fin)
                  )
            );

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return !$query->exists();
    }

    public function calculatePrice(string $dureeType, ?string $heureDebut = null, ?string $heureFin = null): float
    {
        if ($dureeType === 'heure' && $heureDebut && $heureFin) {
            $heures = (strtotime($heureFin) - strtotime($heureDebut)) / 3600;
            return round($heures * $this->prix_heure, 2);
        } elseif ($dureeType === 'journee') {
            return (float) $this->prix_journee;
        } elseif ($dureeType === 'mois') {
            return (float) $this->prix_mois;
        }
        return (float) $this->prix_heure;
    }
}

