<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id', 'user_id', 'numero', 'montant_ht',
        'tva', 'montant_ttc', 'statut', 'date_emission',
        'date_echeance', 'date_paiement', 'methode_paiement', 'notes', 'pdf_path',
    ];

    protected $casts = [
        'date_emission' => 'datetime',
        'date_echeance' => 'datetime',
        'date_paiement' => 'datetime',
        'montant_ht' => 'decimal:2',
        'tva' => 'decimal:2',
        'montant_ttc' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($facture) {
            if (!$facture->numero) {
                $count = static::count() + 1;
                $facture->numero = 'FAC-' . date('Y') . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });
    }

    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function creerPourReservation(Reservation $reservation): self
    {
        $ht = $reservation->prix_total / 1.20;
        return static::create([
            'reservation_id' => $reservation->id,
            'user_id' => $reservation->user_id,
            'montant_ht' => round($ht, 2),
            'tva' => 20.00,
            'montant_ttc' => $reservation->prix_total,
            'statut' => 'emise',
            'date_emission' => now(),
            'date_echeance' => now()->addDays(30),
        ]);
    }

    public function recalculer(): void
    {
        $ht = $this->reservation->prix_total / 1.20;
        $this->montant_ht = round($ht, 2);
        $this->montant_ttc = $this->reservation->prix_total;
        $this->save();
    }

    public function marquerPayee(string $methode = 'virement'): void
    {
        $this->statut = 'payee';
        $this->date_paiement = now();
        $this->methode_paiement = $methode;
        $this->save();
    }
}
