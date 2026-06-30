<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class TerminateExpiredReservationsJob implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
        $this->onQueue('default');
    }

    public function handle(): void
    {
        $count = 0;
        $reservations = Reservation::whereIn('statut', ['confirmee', 'prolongee'])
            ->where('fin', '<=', now()->subMinutes(5))
            ->get();

        foreach ($reservations as $reservation) {
            if (!$reservation->liberation_anticipee) {
                $reservation->statut = 'terminee';
                $reservation->save();
                $count++;
            }
        }

        if ($count > 0) {
            Log::info("TerminateExpiredReservationsJob: {$count} réservation(s) terminée(s).");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('TerminateExpiredReservationsJob échoué', [
            'error' => $exception->getMessage(),
        ]);
    }
}