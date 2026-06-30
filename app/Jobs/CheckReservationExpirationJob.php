<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckReservationExpirationJob implements ShouldQueue
{
    use Queueable;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    protected int $reservationId;

    public function __construct(int $reservationId)
    {
        $this->reservationId = $reservationId;
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $reservation = Reservation::find($this->reservationId);
        
        if (!$reservation) {
            Log::warning("CheckReservationExpirationJob: Réservation #{$this->reservationId} non trouvée.");
            return;
        }

        if (!in_array($reservation->statut, ['confirmee', 'prolongee'])) {
            return;
        }

        if ($reservation->fin <= now()->subMinutes(5)) {
            if (!$reservation->liberation_anticipee) {
                $reservation->statut = 'terminee';
                $reservation->save();
                Log::info("CheckReservationExpirationJob: Réservation #{$this->reservationId} terminée.");
            }
        } else {
            $this->release(60);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("CheckReservationExpirationJob échoué pour #{$this->reservationId}", [
            'error' => $exception->getMessage(),
        ]);
    }
}
