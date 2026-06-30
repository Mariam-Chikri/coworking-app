<?php

namespace App\Livewire;

use App\Models\Espace;
use App\Models\Reservation;
use App\Models\Facture;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\QueryException;

class ReservationForm extends Component
{
    public Espace $espace;

    public string $debut = '';
    public string $fin   = '';
    public int    $nombre_personnes = 1;
    public string $notes  = '';
    public bool   $confirme = false;

    public string $numeroBureau = '';

    public bool  $disponible         = true;
    public bool  $conflitUtilisateur = false;
    public float $prix_estime        = 0;
    public string $erreurMessage = '';

    // =========================================================

    public function mount(Espace $espace): void
    {
        $this->espace           = $espace;
        $this->debut            = now()->addHour()->format('Y-m-d\TH:00');
        $this->fin              = now()->addHours(2)->format('Y-m-d\TH:00');
        $this->nombre_personnes = max(1, $this->espace->capacite_min ?? 1);
        $this->calculerPrix();
        $this->verifierDisponibilite();
    }

    // =========================================================
    // Réactivité Livewire
    // =========================================================

    public function updated(string $field): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->erreurMessage = '';
        $this->disponible = true;
        $this->conflitUtilisateur = false;

        if (in_array($field, ['debut', 'fin', 'nombre_personnes'])) {
            $this->verifierDisponibilite();
            $this->calculerPrix();
            if ($this->espace->type === 'open_space_creatif') {
                $this->numeroBureau = '';
            }
        }
    }

    public function updatedNumeroBureau($value): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->erreurMessage = '';
        
        if (!empty($value)) {
            $this->disponible = true;
            $this->verifierDisponibilite();
        }
    }

    // =========================================================
    // Logique privée
    // =========================================================

    private function verifierDisponibilite(): void
    {
        if (empty($this->debut) || empty($this->fin)) {
            return;
        }

        if (strtotime($this->fin) <= strtotime($this->debut)) {
            $this->disponible = false;
            $this->erreurMessage = app()->getLocale() === 'en' 
                ? 'End date must be after start date.'
                : 'La date de fin doit être après la date de début.';
            return;
        }

        if (strtotime($this->debut) < strtotime('now')) {
            $this->disponible = false;
            $this->erreurMessage = app()->getLocale() === 'en' 
                ? 'Start date must be in the future.'
                : 'La date de début doit être dans le futur.';
            return;
        }

        $this->erreurMessage = '';

        if ($this->espace->type === 'open_space_creatif') {
            $bureauxDisponibles = $this->getBureauxDisponibles();
            $this->disponible = count($bureauxDisponibles) > 0;
            if (!$this->disponible) {
                $this->erreurMessage = app()->getLocale() === 'en'
                    ? 'No available desks for this time slot.'
                    : 'Aucun bureau disponible sur ce créneau.';
                return;
            }
            
            if (!empty($this->numeroBureau)) {
                $bureauExiste = in_array((int)$this->numeroBureau, $bureauxDisponibles);
                if (!$bureauExiste) {
                    $this->disponible = false;
                    $this->erreurMessage = app()->getLocale() === 'en'
                        ? 'The selected desk is no longer available.'
                        : 'Le bureau sélectionné n\'est plus disponible.';
                    $this->numeroBureau = '';
                    return;
                }
            }
        } else {
            $this->disponible = Reservation::espaceDisponible(
                $this->espace->id,
                $this->debut,
                $this->fin
            );
            
            if (!$this->disponible) {
                $this->erreurMessage = app()->getLocale() === 'en' 
                    ? 'This space is already booked on this slot.'
                    : 'Cet espace est déjà réservé sur ce créneau.';
                return;
            }
        }

        $this->conflitUtilisateur = false;
        if (auth()->check()) {
            $this->conflitUtilisateur = !Reservation::utilisateurDisponible(
                auth()->id(),
                $this->debut,
                $this->fin
            );
            
            if ($this->conflitUtilisateur) {
                $this->erreurMessage = app()->getLocale() === 'en'
                    ? 'You already have another reservation on this time slot.'
                    : 'Vous avez déjà une autre réservation sur ce créneau.';
                return;
            }
        }

        $this->erreurMessage = '';
    }

    public function getBureauxDisponibles(): array
    {
        if ($this->espace->type !== 'open_space_creatif') {
            return [];
        }

        $totalBureaux = (int) ($this->espace->nombre_bureaux ?? 0);

        if ($totalBureaux < 1 || empty($this->debut) || empty($this->fin)) {
            return [];
        }

        if (!Schema::hasColumn('reservations', 'numero_bureau')) {
            return range(1, $totalBureaux);
        }

        $reservés = Reservation::where('espace_id', $this->espace->id)
            ->whereIn('statut', ['confirmee', 'prolongee'])
            ->whereNotNull('numero_bureau')
            ->where(function($q) {
                $q->whereBetween('debut', [$this->debut, $this->fin])
                  ->orWhereBetween('fin', [$this->debut, $this->fin])
                  ->orWhere(function($q2) {
                      $q2->where('debut', '<=', $this->debut)
                         ->where('fin', '>=', $this->fin);
                  });
            })
            ->pluck('numero_bureau')
            ->map(fn($v) => (int) $v)
            ->toArray();

        return array_values(array_diff(range(1, $totalBureaux), $reservés));
    }

    private function calculerPrix(): void
    {
        if (empty($this->debut) || empty($this->fin)) {
            $this->prix_estime = 0;
            return;
        }
        try {
            $debut  = new \DateTime($this->debut);
            $fin    = new \DateTime($this->fin);
            $heures = max(0, ($fin->getTimestamp() - $debut->getTimestamp()) / 3600);
            $this->prix_estime = round($heures * $this->espace->prix_heure, 2);
        } catch (\Exception $e) {
            $this->prix_estime = 0;
        }
    }

    // =========================================================
    // Action principale - RÉSERVATION
    // =========================================================

    public function reserver(): void
    {
        $this->resetErrorBag();
        $this->resetValidation();
        $this->erreurMessage = '';
        
        if (!auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        $this->verifierDisponibilite();
        
        if (!$this->disponible) {
            $this->addError('debut', $this->erreurMessage ?: 'Créneau indisponible');
            return;
        }
        
        if ($this->conflitUtilisateur) {
            $this->addError('debut', $this->erreurMessage ?: 'Vous avez déjà une réservation sur ce créneau.');
            return;
        }

        if ($this->espace->type === 'open_space_creatif') {
            if (empty($this->numeroBureau)) {
                $this->addError('numeroBureau', app()->getLocale() === 'en'
                    ? 'Please select a desk number.'
                    : 'Veuillez sélectionner un numéro de bureau.');
                return;
            }
            
            $bureauxDisponibles = $this->getBureauxDisponibles();
            if (!in_array((int)$this->numeroBureau, $bureauxDisponibles)) {
                $this->addError('numeroBureau', app()->getLocale() === 'en'
                    ? 'This desk is not available for the selected time slot.'
                    : 'Ce bureau n\'est pas disponible sur le créneau sélectionné.');
                $this->numeroBureau = '';
                return;
            }
        }

        $rules = [
            'debut'            => 'required|date|after:now',
            'fin'              => 'required|date|after:debut',
            'nombre_personnes' => "required|integer|min:{$this->espace->capacite_min}|max:{$this->espace->capacite_max}",
            'notes'            => 'nullable|string|max:1000',
        ];

        if ($this->espace->type === 'open_space_creatif' && Schema::hasColumn('reservations', 'numero_bureau')) {
            $rules['numeroBureau'] = 'required|integer|min:1|max:' . ($this->espace->nombre_bureaux ?? 0);
        }

        $messages = [
            'numeroBureau.required' => app()->getLocale() === 'en'
                ? 'Please select a desk number.'
                : 'Veuillez sélectionner un numéro de bureau.',
            'numeroBureau.min' => app()->getLocale() === 'en'
                ? 'Invalid desk number.'
                : 'Numéro de bureau invalide.',
            'numeroBureau.max' => app()->getLocale() === 'en'
                ? 'Invalid desk number.'
                : 'Numéro de bureau invalide.',
        ];

        $this->validate($rules, $messages);

        try {
            DB::transaction(function () {
                // Vérifications avec verrou
                if ($this->espace->type === 'open_space_creatif' && Schema::hasColumn('reservations', 'numero_bureau')) {
                    $bureauPris = Reservation::lockForUpdate()
                        ->where('espace_id', $this->espace->id)
                        ->where('numero_bureau', (int) $this->numeroBureau)
                        ->whereIn('statut', ['confirmee', 'prolongee'])
                        ->where(function($q) {
                            $q->whereBetween('debut', [$this->debut, $this->fin])
                              ->orWhereBetween('fin', [$this->debut, $this->fin])
                              ->orWhere(function($q2) {
                                  $q2->where('debut', '<=', $this->debut)
                                     ->where('fin', '>=', $this->fin);
                              });
                        })
                        ->exists();

                    if ($bureauPris) {
                        $this->addError('numeroBureau', app()->getLocale() === 'en'
                            ? 'This desk is already booked for this time slot.'
                            : 'Ce bureau est déjà réservé sur ce créneau.');
                        return;
                    }
                } else {
                    $espaceOk = Reservation::lockForUpdate()
                        ->where('espace_id', $this->espace->id)
                        ->whereIn('statut', ['confirmee', 'prolongee'])
                        ->where(function($q) {
                            $q->whereBetween('debut', [$this->debut, $this->fin])
                              ->orWhereBetween('fin', [$this->debut, $this->fin])
                              ->orWhere(function($q2) {
                                  $q2->where('debut', '<=', $this->debut)
                                     ->where('fin', '>=', $this->fin);
                              });
                        })
                        ->doesntExist();

                    if (!$espaceOk) {
                        $this->addError('debut', app()->getLocale() === 'en'
                            ? 'This space has been booked in the meantime. Please choose another slot.'
                            : 'Cet espace a été réservé entre-temps. Veuillez choisir un autre créneau.');
                        return;
                    }
                }

                $userOk = Reservation::lockForUpdate()
                    ->where('user_id', auth()->id())
                    ->whereIn('statut', ['confirmee', 'prolongee'])
                    ->where(function($q) {
                        $q->whereBetween('debut', [$this->debut, $this->fin])
                          ->orWhereBetween('fin', [$this->debut, $this->fin])
                          ->orWhere(function($q2) {
                              $q2->where('debut', '<=', $this->debut)
                                 ->where('fin', '>=', $this->fin);
                          });
                    })
                    ->doesntExist();

                if (!$userOk) {
                    $this->addError('debut', app()->getLocale() === 'en'
                        ? 'You already have another reservation during this time.'
                        : 'Vous avez déjà une autre réservation pendant ce créneau.');
                    return;
                }

                // --- Créer la réservation ---
                $heures = (strtotime($this->fin) - strtotime($this->debut)) / 3600;
                $prixTotal = round($heures * $this->espace->prix_heure, 2);

                $nouveauNumero = Reservation::generateUniqueNumero();

                $rezData = [
                    'user_id'          => auth()->id(),
                    'espace_id'        => $this->espace->id,
                    'debut'            => $this->debut,
                    'fin'              => $this->fin,
                    'statut'           => 'confirmee',
                    'prix_total'       => $prixTotal,
                    'notes'            => $this->notes,
                    'nombre_personnes' => $this->nombre_personnes,
                    'numero'           => $nouveauNumero,
                ];

                if ($this->espace->type === 'open_space_creatif'
                    && !empty($this->numeroBureau)
                    && Schema::hasColumn('reservations', 'numero_bureau')) {
                    $rezData['numero_bureau'] = (int) $this->numeroBureau;
                }

                $reservation = Reservation::create($rezData);

                // ✅ Essayer de créer la facture, mais ne pas bloquer si elle échoue
                try {
                    Facture::creerPourReservation($reservation);
                } catch (\Exception $e) {
                    // ✅ Log l'erreur mais continue (la réservation est déjà créée)
                    \Log::error('Erreur lors de la création de la facture : ' . $e->getMessage(), [
                        'reservation_id' => $reservation->id
                    ]);
                    // On continue, la réservation est déjà confirmée
                }

                $this->confirme = true;
                $this->erreurMessage = '';
                
                $this->dispatch('toast', message: app()->getLocale() === 'en' 
                    ? 'Booking confirmed successfully!'
                    : 'Réservation confirmée avec succès !', type: 'success');
                $this->dispatch('reservation-creee', id: $reservation->id);
            });
            
        } catch (QueryException $e) {
            // ✅ MESSAGE D'ERREUR DÉTAILLÉ POUR LE DÉBOGAGE
            $errorMessage = $e->getMessage();
            $sql = $e->getSql();
            $bindings = $e->getBindings();
            
            // Log détaillé dans storage/logs/laravel.log
            \Log::error('=== QUERY EXCEPTION DETAILS ===', [
                'message' => $errorMessage,
                'sql' => $sql,
                'bindings' => $bindings,
                'code' => $e->getCode(),
                'user_id' => auth()->id(),
                'espace_id' => $this->espace->id,
                'debut' => $this->debut,
                'fin' => $this->fin,
                'numero_bureau' => $this->numeroBureau,
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            // Si c'est une erreur de duplication, la réservation a peut-être été créée
            if (str_contains($errorMessage, 'Duplicate entry') || str_contains($errorMessage, '1062')) {
                // Vérifier si la réservation a été créée malgré tout
                $lastReservation = Reservation::where('user_id', auth()->id())
                    ->where('espace_id', $this->espace->id)
                    ->where('debut', $this->debut)
                    ->where('fin', $this->fin)
                    ->latest()
                    ->first();
                
                if ($lastReservation) {
                    // ✅ La réservation existe, afficher le succès
                    $this->confirme = true;
                    $this->erreurMessage = '';
                    
                    $this->dispatch('toast', message: app()->getLocale() === 'en' 
                        ? 'Booking confirmed successfully!'
                        : 'Réservation confirmée avec succès !', type: 'success');
                    $this->dispatch('reservation-creee', id: $lastReservation->id);
                    return;
                }
                
                // ✅ AFFICHER L'ERREUR AVEC DÉTAIL (temporaire pour débogage)
                $this->addError('general', 'Erreur SQL: ' . $errorMessage);
            } else {
                // ✅ AFFICHER L'ERREUR COMPLETE (temporaire pour débogage)
                $this->addError('general', 'Erreur SQL: ' . $errorMessage);
            }
            
        } catch (\Exception $e) {
            // ✅ LOG POUR LES AUTRES ERREURS
            \Log::error('=== GENERAL EXCEPTION DETAILS ===', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => auth()->id(),
                'espace_id' => $this->espace->id,
                'debut' => $this->debut,
                'fin' => $this->fin,
                'numero_bureau' => $this->numeroBureau,
            ]);
            
            $errorMessage = $e->getMessage();
            
            // Vérifier si la réservation a été créée malgré l'erreur
            $lastReservation = Reservation::where('user_id', auth()->id())
                ->where('espace_id', $this->espace->id)
                ->where('debut', $this->debut)
                ->where('fin', $this->fin)
                ->latest()
                ->first();
            
            if ($lastReservation) {
                // ✅ La réservation existe, afficher le succès
                $this->confirme = true;
                $this->erreurMessage = '';
                
                $this->dispatch('toast', message: app()->getLocale() === 'en' 
                    ? 'Booking confirmed successfully!'
                    : 'Réservation confirmée avec succès !', type: 'success');
                $this->dispatch('reservation-creee', id: $lastReservation->id);
                return;
            }
            
            // ✅ AFFICHER L'ERREUR COMPLETE (temporaire pour débogage)
            $this->addError('general', 'Erreur: ' . $errorMessage);
        }
    }

    // =========================================================

    public function render()
    {
        $bureauxDisponibles = $this->getBureauxDisponibles();

        return view('livewire.reservation-form', compact('bureauxDisponibles'));
    }
}