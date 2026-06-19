<?php

namespace App\Livewire;

use App\Models\Reservation;
use Livewire\Component;
use Livewire\WithPagination;

class MesReservations extends Component
{
    use WithPagination;

    public string $filtreStatut = '';
    public ?int $reservationAnnulerID = null;
    public ?int $reservationProlongerID = null;
    public int $heuresProlongation = 1;

    public function render()
    {
        $query = auth()->user()->reservations()
            ->with(['espace', 'facture', 'avis'])
            ->latest();

        if ($this->filtreStatut) {
            $query->where('statut', $this->filtreStatut);
        }

        return view('livewire.mes-reservations', [
            'reservations' => $query->paginate(10),
        ]);
    }

    public function confirmerAnnulation(int $id)
    {
        $this->reservationAnnulerID = $id;
    }

    public function annuler()
    {
        $reservation = Reservation::findOrFail($this->reservationAnnulerID);
        if ($reservation->user_id !== auth()->id()) abort(403);

        if ($reservation->debut->isPast()) {
            $this->dispatch('toast', message: __('messages.annulation_passee'), type: 'error');
            return;
        }

        $reservation->update(['statut' => 'annulee']);
        $this->reservationAnnulerID = null;
        $this->dispatch('toast', message: __('messages.reservation_annulee'), type: 'success');
    }

    public function ouvriProlongation(int $id)
    {
        $this->reservationProlongerID = $id;
        $this->heuresProlongation = 1;
    }

    public function prolonger()
    {
        $reservation = Reservation::findOrFail($this->reservationProlongerID);
        if ($reservation->user_id !== auth()->id()) abort(403);

        try {
            $reservation->prolonger($this->heuresProlongation);
            $this->reservationProlongerID = null;
            $this->dispatch('toast', message: __('messages.prolongation_reussie'), type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }
    }

    public function libererAnticipement(int $id)
    {
        $reservation = Reservation::findOrFail($id);
        if ($reservation->user_id !== auth()->id()) abort(403);

        $reservation->libererAnticipement();
        $this->dispatch('toast', message: __('messages.liberation_reussie'), type: 'success');
    }
}
