<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// ✅ Commande inspire (défaut Laravel)
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ✅ Terminer les réservations expirées (toutes les minutes - FALLBACK)
Schedule::command('reservations:terminate-expired')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

// ✅ Nettoyer les jobs échoués (toutes les heures)
Schedule::command('queue:retry all')->hourly();