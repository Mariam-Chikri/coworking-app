<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;

class TerminateExpiredReservations extends Command
{
    protected $signature = 'reservations:terminate-expired {--force : Terminer immédiatement sans vérification}';
    protected $description = 'Termine automatiquement les réservations expirées';

    public function handle()
    {
        $this->info('Recherche des réservations expirées...');

        $reservations = Reservation::whereIn('statut', ['confirmee', 'prolongee'])
            ->where('fin', '<=', now()->subMinutes(5))
            ->get();

        if ($reservations->isEmpty()) {
            $this->info('Aucune réservation expirée trouvée.');
            return Command::SUCCESS;
        }

        $count = 0;
        $bar = $this->output->createProgressBar($reservations->count());

        foreach ($reservations as $reservation) {
            if (!$reservation->liberation_anticipee) {
                $reservation->statut = 'terminee';
                $reservation->save();
                $count++;
                $this->line("  ✅ Réservation #{$reservation->id} ({$reservation->numero}) terminée.");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("✅ {$count} réservation(s) terminée(s) automatiquement.");

        return Command::SUCCESS;
    }
}