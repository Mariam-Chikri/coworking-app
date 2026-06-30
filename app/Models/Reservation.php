<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Reservation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'espace_id', 'debut', 'fin', 'fin_initiale',
        'liberation_anticipee', 'statut', 'prix_total', 'prix_prolongation',
        'notes', 'numero', 'nombre_personnes', 'notif_envoyee', 'numero_bureau',
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
                $reservation->numero = self::generateUniqueNumero();
            }
        });
    }

    // =========================================================
    // ✅ GÉNÉRATION DU NUMÉRO UNIQUE
    // =========================================================

    public static function generateUniqueNumero(): string
    {
        $year = date('Y');
        $prefix = 'REZ-' . $year . '-';
        
        $existingNumbers = self::withTrashed()
            ->where('numero', 'LIKE', $prefix . '%')
            ->pluck('numero')
            ->map(function($num) use ($prefix) {
                $numStr = str_replace($prefix, '', $num);
                return intval($numStr);
            })
            ->sort()
            ->values()
            ->toArray();
        
        if (empty($existingNumbers)) {
            return $prefix . '0001';
        }
        
        $newNumber = 1;
        $maxAttempts = 10000;
        $attempts = 0;
        
        while (in_array($newNumber, $existingNumbers) && $attempts < $maxAttempts) {
            $newNumber++;
            $attempts++;
        }
        
        if ($attempts >= $maxAttempts) {
            $timestamp = date('YmdHis');
            $random = rand(10, 99);
            $numero = $prefix . $timestamp . $random;
            
            if (self::withTrashed()->where('numero', $numero)->exists()) {
                $numero = $prefix . $timestamp . rand(100, 999);
            }
            
            return $numero;
        }
        
        $numero = $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
        
        if (self::withTrashed()->where('numero', $numero)->exists()) {
            $timestamp = date('YmdHis');
            $random = rand(10, 99);
            $numero = $prefix . $timestamp . $random;
        }
        
        return $numero;
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
    // ✅ TERMINER LES RÉSERVATIONS EXPIRÉES (5 min après la fin)
    // =========================================================

    public static function terminateExpiredReservations(): int
    {
        $count = 0;
        $reservations = self::whereIn('statut', ['confirmee', 'prolongee'])
            ->where('fin', '<=', now()->subMinutes(5))
            ->get();

        foreach ($reservations as $reservation) {
            if (!$reservation->liberation_anticipee) {
                $reservation->statut = 'terminee';
                $reservation->save();
                $count++;
            }
        }

        return $count;
    }

    // =========================================================
    // ✅ VÉRIFIER LA DISPONIBILITÉ D'UN ESPACE
    // =========================================================

    public static function espaceDisponible(
        int    $espaceId,
        string $debut,
        string $fin,
        ?int   $excludeId = null
    ): bool {
        $debut = self::formatDate($debut);
        $fin = self::formatDate($fin);

        $query = static::where('espace_id', $espaceId)
            ->whereIn('statut', ['confirmee', 'prolongee'])
            ->where(function($q) use ($debut, $fin) {
                $q->where('debut', '<', $fin)
                  ->where('fin', '>', $debut);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    // =========================================================
    // ✅ VÉRIFIER LA DISPONIBILITÉ D'UN UTILISATEUR
    // =========================================================

    public static function utilisateurDisponible(
        int    $userId,
        string $debut,
        string $fin,
        ?int   $excludeId = null
    ): bool {
        $debut = self::formatDate($debut);
        $fin = self::formatDate($fin);

        $query = static::where('user_id', $userId)
            ->whereIn('statut', ['confirmee', 'prolongee'])
            ->where(function($q) use ($debut, $fin) {
                $q->where('debut', '<', $fin)
                  ->where('fin', '>', $debut);
            });

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    // =========================================================
    // ✅ FORMATEUR DE DATE
    // =========================================================

    private static function formatDate(string $date): string
    {
        if (strpos($date, 'T') !== false) {
            return str_replace('T', ' ', $date) . ':00';
        }
        return $date;
    }

    // =========================================================
    // ✅ Assurer qu'une facture existe
    // =========================================================

    public function ensureFactureExists(): void
    {
        if (!$this->facture && $this->statut === 'confirmee') {
            Facture::creerPourReservation($this);
            $this->load('facture');
        }
    }

    // =========================================================
    // ✅ MÉTHODES MÉTIER - CORRIGÉES DÉFINITIVEMENT
    // =========================================================

    /**
     * Vérifie si la réservation est en cours (période active)
     * ✅ Période active : début <= maintenant <= fin
     */
    public function isEnCours(): bool
    {
        if ($this->statut === 'terminee' || $this->statut === 'annulee') {
            return false;
        }

        return in_array($this->statut, ['confirmee', 'prolongee'])
            && now()->between($this->debut, $this->fin);
    }

    /**
     * ✅ Peut-on prolonger ?
     * Conditions : en cours OU dans les 5 minutes après la fin
     * ET pas libéré ET espace réservable
     */
    public function canBeProlonged(): bool
{
    if ($this->espace && $this->espace->type === 'non_reservable') {
        return false;
    }

    if ($this->liberation_anticipee) {
        return false;
    }

    if (in_array($this->statut, ['terminee','annulee'])) {
        return false;
    }

    if (!in_array($this->statut,['confirmee','prolongee'])) {
        return false;
    }

    return now()->greaterThanOrEqualTo($this->fin)
        && now()->lessThanOrEqualTo(
            $this->fin->copy()->addMinutes(5)
        );
}

    /**
     * ✅ Peut-on libérer anticipativement ?
     * Conditions : en cours ET fin > maintenant ET pas libéré
     */
    public function canBeReleasedEarly(): bool
    {
        // ✅ Vérifier que l'espace est réservable
        if ($this->espace && $this->espace->type === 'non_reservable') {
            return false;
        }

        // ✅ Empêcher si déjà libéré
        if ($this->liberation_anticipee) {
            return false;
        }

        // ✅ Empêcher si déjà terminé ou annulé
        if ($this->statut === 'terminee' || $this->statut === 'annulee') {
            return false;
        }

        // ✅ Vérifier que la réservation est confirmée ou prolongée
        if (!in_array($this->statut, ['confirmee', 'prolongee'])) {
            return false;
        }

        // ✅ La libération est possible UNIQUEMENT si en cours ET fin > maintenant
        return $this->isEnCours() && $this->fin > now();
    }

    /**
     * ✅ Prolonger la réservation
     */
    public function prolonger(int $heures): void
    {
        if ($heures < 1 || $heures > 24) {
            throw new \Exception('Durée de prolongation invalide (1-24h).');
        }

        if (!$this->canBeProlonged()) {
            throw new \Exception('La prolongation n\'est pas possible.');
        }

        $nouvelleFin = $this->fin->copy()->addHours($heures);
        $prixProlongation = $heures * $this->espace->prix_heure;

        // ✅ Vérifier les conflits d'espace
        $conflit = Reservation::where('espace_id', $this->espace_id)
            ->where('id', '!=', $this->id)
            ->whereIn('statut', ['confirmee', 'prolongee'])
            ->where('debut', '<', $nouvelleFin)
            ->where('fin', '>', $this->fin)
            ->exists();

        if ($conflit) {
            throw new \Exception('L\'espace est déjà réservé pendant cette période.');
        }

        // ✅ Vérifier les conflits utilisateur
        $conflitUtilisateur = Reservation::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->whereIn('statut', ['confirmee', 'prolongee'])
            ->where('debut', '<', $nouvelleFin)
            ->where('fin', '>', $this->fin)
            ->exists();

        if ($conflitUtilisateur) {
            throw new \Exception('Vous avez déjà une autre réservation pendant cette période.');
        }

        if (!$this->fin_initiale) {
            $this->fin_initiale = $this->fin->copy();
        }

        $this->fin = $nouvelleFin;
        $this->prix_prolongation = (float)$this->prix_prolongation + $prixProlongation;
        $this->prix_total = (float)$this->prix_total + $prixProlongation;
        $this->statut = 'prolongee';
        $this->save();

        if ($this->facture) {
            $this->facture->recalculer();
        }
    }

    /**
     * ✅ Libérer anticipativement
     */
    public function libererAnticipement(): void
    {
        if (!$this->canBeReleasedEarly()) {
            throw new \Exception('La libération anticipée n\'est pas possible.');
        }

        $maintenant = now();
        $this->liberation_anticipee = $maintenant;
        $this->fin = $maintenant;
        $this->statut = 'terminee';

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

    public function scopeEnCours($query)
    {
        return $query->whereIn('statut', ['confirmee', 'prolongee'])
            ->where('debut', '<=', now())
            ->where('fin', '>=', now());
    }

    public function scopeAVenir($query)
    {
        return $query->whereIn('statut', ['en_attente', 'confirmee', 'prolongee'])
            ->where('debut', '>', now());
    }

    // =========================================================
    // ✅ UTILITAIRES
    // =========================================================

    public static function checkDuplicateNumbers(): array
    {
        $duplicates = self::withTrashed()
            ->select('numero', DB::raw('COUNT(*) as count'))
            ->groupBy('numero')
            ->having('count', '>', 1)
            ->get();

        return $duplicates->toArray();
    }

    public static function cleanDuplicateNumbers(): int
    {
        $count = 0;
        $duplicates = self::withTrashed()
            ->select('numero', DB::raw('MIN(id) as min_id'))
            ->groupBy('numero')
            ->having('count', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $deleted = self::withTrashed()
                ->where('numero', $dup->numero)
                ->where('id', '!=', $dup->min_id)
                ->forceDelete();
            
            $count += $deleted;
        }

        return $count;
    }
}