<?php

namespace App\Http\Middleware;

use App\Models\Reservation;
use Closure;
use Illuminate\Http\Request;

class TerminateReservations
{
    public function handle(Request $request, Closure $next)
    {
        // À CHAQUE REQUÊTE HTTP, terminer les réservations expirées
        Reservation::terminateExpiredReservations();
        
        return $next($request);
    }
}