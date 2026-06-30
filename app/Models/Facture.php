<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id', 
        'user_id', 
        'numero', 
        'montant_ht',
        'tva', 
        'montant_ttc', 
        'statut', 
        'date_emission',
        'date_echeance', 
        'date_paiement', 
        'methode_paiement', 
        'notes', 
        'pdf_path',
    ];

    protected $casts = [
        'date_emission' => 'datetime',
        'date_echeance' => 'datetime',
        'date_paiement' => 'datetime',
        'montant_ht' => 'decimal:2',
        'tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    // =========================================================
    // Boot
    // =========================================================

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($facture) {
            if (!$facture->numero) {
                $facture->numero = self::generateUniqueNumero();
            }
        });
    }

    // =========================================================
    // ✅ GÉNÉRATION SÉCURISÉE DU NUMÉRO DE FACTURE
    // =========================================================

    public static function generateUniqueNumero(): string
    {
        $year = date('Y');
        $prefix = 'FAC-' . $year . '-';
        
        $existingNumbers = self::where('numero', 'LIKE', $prefix . '%')
            ->pluck('numero')
            ->map(function($num) {
                return intval(substr($num, -4));
            })
            ->sort()
            ->values()
            ->toArray();
        
        $newNumber = 1;
        $maxAttempts = 10000;
        $attempts = 0;
        
        while (in_array($newNumber, $existingNumbers) && $attempts < $maxAttempts) {
            $newNumber++;
            $attempts++;
        }
        
        $numero = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        
        if (self::where('numero', $numero)->exists()) {
            $numero = $prefix . date('YmdHis') . rand(10, 99);
        }
        
        return $numero;
    }

    // =========================================================
    // Relations
    // =========================================================

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // =========================================================
    // ✅ CRÉER UNE FACTURE POUR UNE RÉSERVATION
    // =========================================================

    public static function creerPourReservation(Reservation $reservation): self
    {
        if ($reservation->facture) {
            return $reservation->facture;
        }

        $tva = 20.00;
        $ht = $reservation->prix_total / (1 + $tva / 100);

        return static::create([
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
            'montant_ht' => round($ht, 2),
            'tva' => $tva,
            'montant_ttc' => $reservation->prix_total,
            'statut' => 'emise',
            'date_emission' => now(),
            'date_echeance' => now()->addDays(30),
            'notes' => 'Facture pour la réservation ' . $reservation->numero,
            'pdf_path' => null,
        ]);
    }

    // =========================================================
    // ✅ RECALCULER LA FACTURE
    // =========================================================

    public function recalculer(): void
    {
        if (!$this->reservation) {
            return;
        }

        $tva = 20.00;
        $ht = $this->reservation->prix_total / (1 + $tva / 100);

        $this->montant_ht = round($ht, 2);
        $this->montant_ttc = $this->reservation->prix_total;
        $this->save();
    }

    // =========================================================
    // ✅ MARQUER LA FACTURE
    // =========================================================

    public function marquerPayee(string $methode = 'carte'): void
    {
        $this->statut = 'payee';
        $this->date_paiement = now();
        $this->methode_paiement = $methode;
        $this->save();
    }

    public function marquerAnnulee(): void
    {
        $this->statut = 'annulee';
        $this->save();
    }

    public function marquerEmise(): void
    {
        $this->statut = 'emise';
        $this->date_paiement = null;
        $this->methode_paiement = null;
        $this->save();
    }

    // =========================================================
    // ✅ RECRÉER LES FACTURES MANQUANTES
    // =========================================================

    public static function recreateMissingFactures(): int
    {
        $reservations = Reservation::where('statut', 'confirmee')
            ->whereDoesntHave('facture')
            ->get();

        $count = 0;
        foreach ($reservations as $reservation) {
            try {
                self::creerPourReservation($reservation);
                $count++;
            } catch (\Exception $e) {
                continue;
            }
        }

        return $count;
    }

    // =========================================================
    // ✅ ACCESSORS FORMATÉS
    // =========================================================

    public function getMontantHTFormattedAttribute(): string
    {
        return number_format($this->montant_ht, 2, ',', ' ') . ' MAD';
    }

    public function getMontantTTCFormattedAttribute(): string
    {
        return number_format($this->montant_ttc, 2, ',', ' ') . ' MAD';
    }

    public function getStatutLabelAttribute(): string
    {
        return [
            'emise' => 'Émise',
            'payee' => 'Payée',
            'annulee' => 'Annulée',
        ][$this->statut] ?? $this->statut;
    }

    public function getStatutBadgeClassAttribute(): string
    {
        return [
            'emise' => 'en_attente',
            'payee' => 'confirmee',
            'annulee' => 'annulee',
        ][$this->statut] ?? 'en_attente';
    }

    // =========================================================
    // ✅ SCOPES
    // =========================================================

    public function scopeEmises($query)
    {
        return $query->where('statut', 'emise');
    }

    public function scopePayees($query)
    {
        return $query->where('statut', 'payee');
    }

    public function scopeAnnulees($query)
    {
        return $query->where('statut', 'annulee');
    }
}