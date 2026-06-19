<?php

namespace App\Livewire;

use App\Models\Avis;
use App\Models\Espace;
use App\Models\Reservation;
use Livewire\Component;

class AvisComponent extends Component
{
    public Espace $espace;
    public Reservation $reservation;
    public int $note = 5;
    public string $commentaire = '';
    public bool $soumis = false;

    public function mount(Espace $espace, Reservation $reservation)
    {
        $this->espace = $espace;
        $this->reservation = $reservation;
    }

    public function soumettre()
    {
        $this->validate([
            'note' => 'required|integer|between:1,5',
            'commentaire' => 'nullable|string|max:2000',
        ]);

        Avis::updateOrCreate(
            ['user_id' => auth()->id(), 'reservation_id' => $this->reservation->id],
            [
                'espace_id' => $this->espace->id,
                'note' => $this->note,
                'commentaire' => $this->commentaire,
                'valide' => false,
            ]
        );

        $this->soumis = true;
        $this->dispatch('toast', message: __('messages.avis_soumis'), type: 'success');
    }

    public function render()
    {
        return view('livewire.avis-component');
    }
}
