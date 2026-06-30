<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;

class TerminateExpiredReservationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:terminate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Terminer automatiquement les réservations expirées (après 5 min de grâce)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Vérification des réservations expirées...');

        $graceMinutes = 5;
        
        $reservations = Reservation::whereIn('statut', ['confirmee', 'prolongee'])
            ->where('fin', '<=', now()->subMinutes($graceMinutes))
            ->where(function ($query) {
                $query->whereNull('liberation_anticipee')
                      ->orWhere('liberation_anticipee', false);
            })
            ->get();

        $count = 0;
        foreach ($reservations as $reservation) {
            $reservation->update(['statut' => 'terminee']);
            $this->line("✅ Réservation #{$reservation->numero} terminée");
            $count++;
        }

        $this->info("✅ {$count} réservation(s) terminée(s)");
        return Command::SUCCESS;
    }
}
