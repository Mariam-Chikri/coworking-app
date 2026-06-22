<?php

namespace App\Livewire;

use App\Models\Espace;
use App\Models\Reservation;
use App\Models\Facture;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ReservationForm extends Component
{
    public Espace $espace;

    public string $debut = '';
    public string $fin   = '';
    public int    $nombre_personnes = 1;
    public string $notes  = '';
    public bool   $confirme = false;

    // État de disponibilité (mis à jour en temps réel via Livewire)
    public bool  $disponible         = true;
    public bool  $conflitUtilisateur = false; // L'utilisateur a déjà une réservation simultanée
    public float $prix_estime        = 0;

    // =========================================================

    public function mount(Espace $espace): void
    {
        $this->espace           = $espace;
        $this->debut            = now()->addHour()->format('Y-m-d\TH:00');
        $this->fin              = now()->addHours(2)->format('Y-m-d\TH:00');
        $this->nombre_personnes = max(1, $this->espace->capacite_min ?? 1);
        $this->calculerPrix();
    }

    // =========================================================
    // Réactivité Livewire
    // =========================================================

    public function updated(string $field): void
    {
        if (in_array($field, ['debut', 'fin'])) {
            $this->verifierDisponibilite();
            $this->calculerPrix();
        }
    }

    // =========================================================
    // Logique privée
    // =========================================================

    /**
     * Vérifie deux règles de chevauchement en temps réel :
     *   1. L'ESPACE doit être libre sur ce créneau.
     *   2. L'UTILISATEUR connecté ne doit pas avoir d'autre réservation simultanée.
     */
    private function verifierDisponibilite(): void
    {
        if (!$this->debut || !$this->fin) {
            return;
        }

        // --- Règle 1 : espace libre ---
        $this->disponible = Reservation::espaceDisponible(
            $this->espace->id,
            $this->debut,
            $this->fin
        );

        // --- Règle 2 : utilisateur libre (si connecté) ---
        $this->conflitUtilisateur = false;
        if (auth()->check()) {
            $this->conflitUtilisateur = !Reservation::utilisateurDisponible(
                auth()->id(),
                $this->debut,
                $this->fin
            );
        }
    }

    private function calculerPrix(): void
    {
        if (!$this->debut || !$this->fin) {
            return;
        }
        try {
            $debut  = new \DateTime($this->debut);
            $fin    = new \DateTime($this->fin);
            $heures = max(0, ($fin->getTimestamp() - $debut->getTimestamp()) / 3600);
            $this->prix_estime = round($heures * $this->espace->prix_heure, 2);
        } catch (\Exception) {
            $this->prix_estime = 0;
        }
    }

    // =========================================================
    // Action principale
    // =========================================================

    public function reserver(): void
    {
        if (!auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        $this->validate([
            'debut'            => 'required|date|after:now',
            'fin'              => 'required|date|after:debut',
            'nombre_personnes' => "required|integer|min:{$this->espace->capacite_min}|max:{$this->espace->capacite_max}",
            'notes'            => 'nullable|string|max:1000',
        ]);

        try {
            DB::transaction(function () {

                // --- Double vérification avec verrou (anti race-condition) ---

                // Règle 1 : L'espace doit être libre
                $espaceOk = Reservation::lockForUpdate()
                    ->where('espace_id', $this->espace->id)
                    ->whereNotIn('statut', ['annulee'])
                    ->where('debut', '<', $this->fin)
                    ->where('fin', '>', $this->debut)
                    ->doesntExist();
                if (!$espaceOk) {
                    $this->addError('debut', __('messages.creneau_indisponible'));
                    return;
                }

                // Règle 2 : L'utilisateur ne doit pas avoir de réservation simultanée
                $userOk = Reservation::lockForUpdate()
                    ->where('user_id', auth()->id())
                    ->whereNotIn('statut', ['annulee'])
                    ->where('debut', '<', $this->fin)
                    ->where('fin', '>', $this->debut)
                    ->doesntExist();
                if (!$userOk) {
                    $this->addError(
    'debut',
    'Vous avez déjà une autre réservation pendant cette période.'
);
                    return;
                }

                // --- Créer la réservation ---
                $heures    = (strtotime($this->fin) - strtotime($this->debut)) / 3600;
                $prixTotal = round($heures * $this->espace->prix_heure, 2);

                $reservation = Reservation::create([
                    'user_id'          => auth()->id(),
                    'espace_id'        => $this->espace->id,
                    'debut'            => $this->debut,
                    'fin'              => $this->fin,
                    'statut'           => 'confirmee',
                    'prix_total'       => $prixTotal,
                    'notes'            => $this->notes,
                    'nombre_personnes' => $this->nombre_personnes,
                ]);

                Facture::creerPourReservation($reservation);

                $this->confirme = true;
                $this->dispatch('toast', message: __('messages.reservation_confirmee'), type: 'success');
                $this->dispatch('reservation-creee', id: $reservation->id);
            });
        } catch (\Exception $e) {
            $this->addError('general', $e->getMessage());
        }
    }

    // =========================================================

    public function render()
    {
        return view('livewire.reservation-form');
    }
}

