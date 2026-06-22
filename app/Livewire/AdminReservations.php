<?php

namespace App\Livewire;

use App\Models\Reservation;
use App\Models\Espace;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AdminReservations extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatut = '';
    public string $filterEspace = '';
    public string $dateDebut = '';
    public string $dateFin = '';

    public bool $showModal = false;
    public bool $showCancelModal = false;
    public ?int $reservationId = null;
    public ?Reservation $selected = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatut(): void { $this->resetPage(); }
    public function updatingFilterEspace(): void { $this->resetPage(); }

    public function voir(int $id): void
    {
        $this->selected = Reservation::with(['user', 'espace', 'facture'])->findOrFail($id);
        $this->showModal = true;
    }

    public function confirmer(int $id): void
    {
        $r = Reservation::findOrFail($id);
        $r->update(['statut' => 'confirmee']);
        $this->dispatch('toast', message: 'Réservation confirmée', type: 'success');
        if ($this->selected && $this->selected->id === $id) {
            $this->selected = $r->fresh(['user', 'espace', 'facture']);
        }
    }

    public function confirmerAnnulation(int $id): void
    {
        $this->reservationId = $id;
        $this->showCancelModal = true;
    }

    public function annuler(): void
    {
        $r = Reservation::findOrFail($this->reservationId);
        $r->update(['statut' => 'annulee']);
        $this->showCancelModal = false;
        $this->reservationId = null;
        $this->dispatch('toast', message: 'Réservation annulée', type: 'info');
    }

    public function terminer(int $id): void
    {
        Reservation::findOrFail($id)->update(['statut' => 'terminee']);
        $this->dispatch('toast', message: 'Réservation marquée terminée', type: 'success');
    }

    public function render()
    {
        $reservations = Reservation::with(['user:id,name,email', 'espace:id,nom'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"))
                  ->orWhere('numero', 'like', "%{$this->search}%");
            })
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterEspace, fn($q) => $q->where('espace_id', $this->filterEspace))
            ->when($this->dateDebut, fn($q) => $q->whereDate('debut', '>=', $this->dateDebut))
            ->when($this->dateFin, fn($q) => $q->whereDate('debut', '<=', $this->dateFin))
            ->latest()
            ->paginate(15);

        $espaces = Espace::orderBy('nom')->get(['id', 'nom']);

        $stats = [
            'total' => Reservation::count(),
            'en_attente' => Reservation::where('statut', 'en_attente')->count(),
            'confirmees' => Reservation::where('statut', 'confirmee')->count(),
            'annulees' => Reservation::where('statut', 'annulee')->count(),
        ];

        return view('livewire.admin-reservations', compact('reservations', 'espaces', 'stats'));
    }
}
