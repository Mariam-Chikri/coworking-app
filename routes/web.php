<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\AboutImageController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/


// Changement de langue (sans reload via session)
Route::get('/lang/{locale}', function ($locale) {
    if (in_array($locale, ['fr', 'en'])) {
        session()->put('locale', $locale);
    }
    return redirect()->back();
})->name('lang.switch');

// Page d'accueil
Route::get('/', fn() => view('home'))->name('home');

// Espaces (publics)
Route::get('/espaces', fn() => view('espaces.index'))
    ->name('espaces.index');

Route::get('/espaces/{espace}', function (\App\Models\Espace $espace) {
    return view('espaces.show', compact('espace'));
})->name('espaces.show');

// Authentification Breeze
require __DIR__.'/auth.php';

// Routes protégées (utilisateurs authentifiés)
Route::middleware(['auth'])->group(function () {

    // Réservations
    Route::get('/reservations', fn() => view('reservations.index'))
        ->name('reservations.index');

    Route::get('/reservations/{reservation}', function (\App\Models\Reservation $reservation) {
        abort_unless(
            auth()->id() === $reservation->user_id || auth()->user()?->is_admin,
            403
        );
        return view('reservations.show', compact('reservation'));
    })->name('reservations.show');

    // Favoris
    Route::get('/favoris', fn() => view('favoris.index'))
        ->name('favoris');

    // Factures
    Route::get('/factures', fn() => view('factures.index'))
        ->name('factures.index');

    Route::get('/factures/{facture}/pdf', [\App\Http\Controllers\Api\FactureController::class, 'telecharger'])
        ->name('factures.pdf');

    // Profil utilisateur (vue personnalisée)
    Route::get('/profil', fn() => view('profile.show'))
        ->name('profile');

    Route::patch('/profil', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::put('/profil/password', [ProfileController::class, 'updatePassword'])
        ->name('profile.password');

    // Dashboard Breeze (optionnel)
    Route::get('/dashboard', fn() => view('dashboard'))
        ->middleware('verified')
        ->name('dashboard');

    // Routes Breeze Profile (/profile)
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');

    // Administration (middleware admin requis)
    Route::middleware(['admin'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/dashboard', fn() => view('admin.dashboard'))
                ->name('dashboard');

            Route::get('/espaces', fn() => view('admin.espaces'))
                ->name('espaces');

            Route::get('/reservations', fn() => view('admin.reservations'))
                ->name('reservations');

            Route::get('/utilisateurs', fn() => view('admin.utilisateurs'))
                ->name('utilisateurs');

            Route::get('/avis', fn() => view('admin.avis'))
                ->name('avis');

            Route::get('/factures', fn() => view('admin.factures'))
                ->name('factures');
                   });
 
            Route::delete('/delete-about-image/{section}', [AboutImageController::class, 'destroy'])
               ->name('admin.delete-about-image');
            
});

