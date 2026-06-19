<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    AuthController,
    EspaceController,
    ReservationController,
    FactureController,
    AvisController,
    FavoriController,
    StatsController,
};

/*
|--------------------------------------------------------------------------
| API Routes (Sanctum)
|--------------------------------------------------------------------------
*/

// Authentification
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Routes publiques
Route::get('/espaces', [EspaceController::class, 'index']);
Route::get('/espaces/{espace}', [EspaceController::class, 'show']);
Route::get('/espaces/{espace}/disponibilite', [EspaceController::class, 'disponibilite']);
Route::get('/espaces/{espace}/avis', [AvisController::class, 'index']);

// Routes authentifiées
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);

    // Espaces
    Route::get('/espaces/{espace}/stats', [EspaceController::class, 'stats']);

    // Réservations
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show']);
    Route::post('/reservations/{reservation}/prolonger', [ReservationController::class, 'prolonger']);
    Route::post('/reservations/{reservation}/liberer', [ReservationController::class, 'libererAnticipement']);
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'annuler']);

    // Factures
    Route::get('/factures', [FactureController::class, 'index']);
    Route::get('/factures/{facture}', [FactureController::class, 'show']);
    Route::get('/factures/{facture}/pdf', [FactureController::class, 'telecharger']);

    // Avis
    Route::post('/avis', [AvisController::class, 'store']);
    Route::delete('/avis/{avis}', [AvisController::class, 'destroy']);

    // Favoris
    Route::get('/favoris', [FavoriController::class, 'index']);
    Route::post('/espaces/{espace}/favoris', [FavoriController::class, 'toggle']);

    // Admin
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/stats', [StatsController::class, 'dashboard']);
        Route::get('/stats/espaces', [StatsController::class, 'espaceStats']);
        Route::get('/reservations', [ReservationController::class, 'adminIndex']);
        Route::post('/reservations/{reservation}/confirmer', [ReservationController::class, 'adminConfirmer']);
        Route::post('/espaces', [EspaceController::class, 'store']);
        Route::put('/espaces/{espace}', [EspaceController::class, 'update']);
        Route::delete('/espaces/{espace}', [EspaceController::class, 'destroy']);
        Route::post('/avis/{avis}/valider', [AvisController::class, 'valider']);
        Route::delete('/avis/{avis}', [AvisController::class, 'destroy']);
        Route::post('/factures/{facture}/payer', [FactureController::class, 'marquerPayee']);
    });
});
