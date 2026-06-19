<?php

namespace App\Http\Livewire;

use App\Models\Espace;
use App\Models\Reservation;
use App\Models\Facture;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class ReservationForm extends Component
{
    public Espace $espace;
    public string $debut = '';
    public string $fin = '';
    public int $nombre_personnes = 1;
    public string $notes = '';
    public bool $disponible = true;
    public float $prix_estime = 0;
    public bool $confirme = false;

    public function mount(Espace $espace)
    {
        $this->espace = $espace;
        $this->debut = now()->addHour()->format('Y-m-d\TH:00');
        $this->fin = now()->addHours(2)->format('Y-m-d\TH:00');
        $this->calculerPrix();
    }

    public function updated($field)
    {
        if (in_array($field, ['debut', 'fin'])) {
            $this->verifierDisponibilite();
            $this->calculerPrix();
        }
    }

    private function verifierDisponibilite()
    {
        if (!$this->debut || !$this->fin) return;

        $this->disponible = !Reservation::where('espace_id', $this->espace->id)
            ->whereNotIn('statut', ['annulee'])
            ->where(fn($q) =>
                $q->whereBetween('debut', [$this->debut, $this->fin])
                  ->orWhereBetween('fin', [$this->debut, $this->fin])
                  ->orWhere(fn($q2) => $q2->where('debut', '<=', $this->debut)->where('fin', '>=', $this->fin))
            )->exists();
    }

    private function calculerPrix()
    {
        if (!$this->debut || !$this->fin) return;
        try {
            $debut = new \DateTime($this->debut);
            $fin = new \DateTime($this->fin);
            $heures = max(0, ($fin->getTimestamp() - $debut->getTimestamp()) / 3600);
            $this->prix_estime = round($heures * $this->espace->prix_heure, 2);
        } catch (\Exception $e) {
            $this->prix_estime = 0;
        }
    }

    public function reserver()
    {
        $this->validate([
            'debut' => 'required|date|after:now',
            'fin' => 'required|date|after:debut',
            'nombre_personnes' => "required|integer|min:1|max:{$this->espace->capacite}",
            'notes' => 'nullable|string|max:1000',
        ]);

        if (!$this->disponible) {
            $this->addError('debut', __('messages.creneau_indisponible'));
            return;
        }

        try {
            DB::transaction(function () {
                $debut = new \DateTime($this->debut);
                $fin = new \DateTime($this->fin);
                $heures = ($fin->getTimestamp() - $debut->getTimestamp()) / 3600;
                $prixTotal = round($heures * $this->espace->prix_heure, 2);

                $reservation = Reservation::create([
                    'user_id' => auth()->id(),
                    'espace_id' => $this->espace->id,
                    'debut' => $this->debut,
                    'fin' => $this->fin,
                    'statut' => 'confirmee',
                    'prix_total' => $prixTotal,
                    'notes' => $this->notes,
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

    public function render()
    {
        return view('livewire.reservation-form');
    }
}
