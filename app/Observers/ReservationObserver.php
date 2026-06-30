<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Jobs\CheckReservationExpirationJob;
use App\Jobs\TerminateExpiredReservationsJob;
use Illuminate\Support\Facades\Log;

class ReservationObserver
{
    /**
     * Après la création d'une réservation
     */
    public function created(Reservation $reservation): void
    {
        // Planifier la vérification automatique
        $this->scheduleExpirationCheck($reservation);
    }

    /**
     * Après la mise à jour d'une réservation
     */
    public function updated(Reservation $reservation): void
    {
        // Si le statut ou la fin a changé, replanifier
        if ($reservation->wasChanged(['statut', 'fin'])) {
            $this->scheduleExpirationCheck($reservation);
        }
    }

    /**
     * Planifier la vérification d'expiration
     */
    private function scheduleExpirationCheck(Reservation $reservation): void
    {
        // Uniquement pour les réservations confirmées ou prolongées
        if (!in_array($reservation->statut, ['confirmee', 'prolongee'])) {
            return;
        }

        // Planifier un job pour 5 minutes après la fin
        $delay = $reservation->fin->addMinutes(5);
        
        // Vérifier si le job existe déjà dans le futur
        // (On utilisera un job unique pour chaque réservation)
        
        // Dispatch un job individuel pour cette réservation
        CheckReservationExpirationJob::dispatch($reservation->id)
            ->delay($delay);
        
        Log::info("ReservationObserver: Job planifié pour la réservation #{$reservation->id} à " . $delay->format('Y-m-d H:i:s'));
    }
}