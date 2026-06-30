<?php

namespace App\Livewire;

use App\Models\Reservation;
use Livewire\Component;

class ReservationActions extends Component
{
    public Reservation $reservation;
    public bool $showProlongModal = false;
    public int $heuresProlongation = 1;
    public string $erreurProlongation = '';

    public function mount(Reservation $reservation)
    {
        $this->reservation = $reservation;
    }

    public function render()
    {
        // ✅ Terminer les réservations expirées à CHAQUE rendu (y compris les polls Livewire)
        // Le middleware TerminateReservations ne s'exécute pas sur les requêtes XHR Livewire
        \App\Models\Reservation::terminateExpiredReservations();

        // Rafraîchir depuis la BDD à chaque poll
        $this->reservation->refresh();

        return view('livewire.reservation-actions', [
            'reservation'  => $this->reservation,
            'isEnCours'    => $this->reservation->isEnCours(),
            'canProlong'   => $this->reservation->canBeProlonged(),
            'canRelease'   => $this->reservation->canBeReleasedEarly(),
            'estTerminee'  => in_array($this->reservation->statut, ['terminee', 'annulee']),
        ]);
    }

    public function ouvrirProlongation()
    {
        if (!$this->reservation->canBeProlonged()) {
            $this->dispatch('toast', message: 'La prolongation n\'est pas possible.', type: 'error');
            return;
        }

        $this->showProlongModal = true;
        $this->heuresProlongation = 1;
        $this->erreurProlongation = '';
    }

    public function fermerProlongation()
    {
        $this->showProlongModal = false;
        $this->erreurProlongation = '';
    }

    public function prolonger()
    {
        if ($this->reservation->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            if (!$this->reservation->canBeProlonged()) {
                $this->erreurProlongation = 'La prolongation n\'est pas possible (délai de grâce écoulé).';
                return;
            }

            $this->reservation->prolonger($this->heuresProlongation);
            $this->reservation->refresh();
            $this->showProlongModal = false;
            $this->erreurProlongation = '';
            $this->dispatch('toast', message: __('messages.prolongation_reussie'), type: 'success');
        } catch (\Exception $e) {
            $this->erreurProlongation = $e->getMessage();
        }
    }

    public function libererAnticipement()
    {
        if ($this->reservation->user_id !== auth()->id()) {
            abort(403);
        }

        try {
            if (!$this->reservation->canBeReleasedEarly()) {
                $this->dispatch('toast', message: __('messages.liberation_impossible'), type: 'error');
                return;
            }

            $this->reservation->libererAnticipement();
            $this->reservation->refresh();
            $this->dispatch('toast', message: __('messages.liberation_reussie'), type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }
}

