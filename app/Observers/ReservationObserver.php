<?php

namespace App\Observers;

use App\Models\Reservation;
use App\Jobs\TerminateExpiredReservationsJob;

class ReservationObserver
{
    /**
     * Handle the Reservation "created" event.
     */
    public function created(Reservation $reservation): void
    {
        // Dispatcher un job pour vérifier les réservations expirées après création
        TerminateExpiredReservationsJob::dispatch()->delay(now()->addMinutes(1));
    }

    /**
     * Handle the Reservation "updated" event.
     */
    public function updated(Reservation $reservation): void
    {
        // Si le statut passe à 'terminee', déclencher les événements appropriés
        if ($reservation->isDirty('statut') && $reservation->statut === 'terminee') {
            info("Réservation #{$reservation->numero} terminée.");
        }
    }

    /**
     * Handle the Reservation "deleted" event.
     */
    public function deleted(Reservation $reservation): void
    {
        //
    }

    /**
     * Handle the Reservation "restored" event.
     */
    public function restored(Reservation $reservation): void
    {
        //
    }

    /**
     * Handle the Reservation "force deleted" event.
     */
    public function forceDeleted(Reservation $reservation): void
    {
        //
    }
}