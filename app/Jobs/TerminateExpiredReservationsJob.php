<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class TerminateExpiredReservationsJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     * 
     * Terminer automatiquement les réservations après la période de grâce (5 minutes)
     */
    public function handle(): void
    {
        try {
            // Grace period: 5 minutes après la fin
            $graceMinutes = 5;
            
            // Récupérer les réservations confirmées ou prolongées
            // dont la fin + 5 minutes est passée
            $reservations = Reservation::whereIn('statut', ['confirmee', 'prolongee'])
                ->where('fin', '<=', now()->subMinutes($graceMinutes))
                ->where(function ($query) {
                    $query->whereNull('liberation_anticipee')
                          ->orWhere('liberation_anticipee', false);
                })
                ->get();

            $count = 0;
            foreach ($reservations as $reservation) {
                $reservation->update([
                    'statut' => 'terminee'
                ]);
                
                Log::info("Réservation #{$reservation->numero} automatiquement terminée (fin : {$reservation->fin})");
                $count++;
            }

            if ($count > 0) {
                Log::info("✅ {$count} réservation(s) terminée(s) automatiquement");
            }

        } catch (\Exception $e) {
            Log::error('❌ Erreur lors de la terminaison des réservations : ' . $e->getMessage());
            throw $e;
        }
    }
}
