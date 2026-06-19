<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Avis;

class Espace extends Model
{
    use HasFactory;

    protected $table = 'espaces';

    protected $fillable = [
        'nom', 'type', 'description', 'capacite_min', 'capacite_max',
        'prix_heure', 'prix_journee', 'prix_mois', 'photo_principale',
        'photos', 'equipements', 'adresse', 'latitude', 'longitude', 'actif'
    ];

    protected $casts = [
        'photos' => 'array',
        'equipements' => 'array',
        'actif' => 'boolean',
    ];

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function isAvailable($date, $heureDebut, $heureFin, $excludeReservationId = null)
    {
        $query = $this->reservations()
            ->where('date_reservation', $date)
            ->where('statut', 'confirmee')
            ->where(function ($q) use ($heureDebut, $heureFin) {
                $q->where('heure_debut', '<', $heureFin)
                  ->where('heure_fin', '>', $heureDebut);
            });

        if ($excludeReservationId) {
            $query->where('id', '!=', $excludeReservationId);
        }

        return !$query->exists();
    }

    public function calculatePrice($dureeType, $heureDebut = null, $heureFin = null)
    {
        if ($dureeType === 'heure' && $heureDebut && $heureFin) {
            $heures = (strtotime($heureFin) - strtotime($heureDebut)) / 3600;
            return round($heures * $this->prix_heure, 2);
        } elseif ($dureeType === 'journee') {
            return $this->prix_journee;
        } elseif ($dureeType === 'mois') {
            return $this->prix_mois;
        }
        return $this->prix_heure;
    }

    public function getTypeLabelAttribute()
    {
        return [
            'bureau_individuel' => 'Bureau Individuel',
            'bureau_prive' => 'Bureau Privé',
            'open_space' => 'Open Space',
            'salle_reunion' => 'Salle de Réunion',
            'salle_conference' => 'Salle de Conférence'
        ][$this->type] ?? $this->type;
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo_principale && \Storage::disk('public')->exists($this->photo_principale)) {
            return asset('storage/' . $this->photo_principale);
        }

        return "https://picsum.photos/seed/{$this->id}/400/300";
    }

    public function getPrixHeureFormattedAttribute()
    {
        return number_format($this->prix_heure, 2, ',', ' ') . ' MAD';
    }

    public function getPrixJourneeFormattedAttribute()
    {
        return $this->prix_journee ? number_format($this->prix_journee, 2, ',', ' ') . ' MAD' : null;
    }

    public function getPrixMoisFormattedAttribute()
    {
        return $this->prix_mois ? number_format($this->prix_mois, 2, ',', ' ') . ' MAD' : null;
    }

    // ✅ CORRECTION ICI
    public function scopeActive($query)
    {
        return $query->where('actif', 1);
    }

    public function scopeByType($query, $type)
    {
        return $type ? $query->where('type', $type) : $query;
    }

    public function scopeReservable($query)
    {
        return $query->where('actif', true);
    }
    public function scopeNonReservable($query)
    {
        return $query->where('actif', false);
    }
    public function avis()
    {
        return $this->hasMany(Avis::class);
    }
    public function scopeSearch($query, $search)
    {
        return $search
            ? $query->where('nom', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
            : $query;
    }
}