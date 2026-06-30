<?php

namespace App\Livewire;

use App\Models\Reservation;
use App\Models\Espace;
use App\Models\User;
use Livewire\Component;

class AdminReservations extends Component
{
    public string $search = '';
    public string $filterStatut = '';
    public string $filterEspace = '';
    public string $dateDebut = '';
    public string $dateFin = '';

    public bool $showModal = false;
    public bool $showCancelModal = false;
    public bool $showDeleteModal = false; 
    public ?int $reservationId = null;
    public ?int $deleteId = null; 
    public ?Reservation $selected = null;

    public function updatedSearch(): void 
    { 
        // Pas de resetPage nécessaire
    }
    
    public function updatedFilterStatut(): void 
    { 
        // Pas de resetPage nécessaire
    }
    
    public function updatedFilterEspace(): void 
    { 
        // Pas de resetPage nécessaire
    }

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

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        try {
            $reservation = Reservation::findOrFail($this->deleteId);
            
            if ($reservation->facture) {
                $reservation->facture->delete();
            }
            
            $reservation->delete();
            $this->showDeleteModal = false;
            $this->deleteId = null;
            $this->dispatch('toast', message: 'Réservation supprimée avec succès.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Erreur lors de la suppression.', type: 'error');
        }
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterStatut = '';
        $this->filterEspace = '';
        $this->dateDebut = '';
        $this->dateFin = '';
    }

    public function render()
    {
        // ✅ Terminer les réservations expirées AVANT de charger la liste
        Reservation::terminateExpiredReservations();

        $reservations = Reservation::with(['user:id,name,email', 'espace:id,nom'])
            ->when($this->search, function ($q) {
                $q->where(function($sub) {
                    $sub->whereHas('user', fn($u) => 
                        $u->where('name', 'like', "%{$this->search}%")
                          ->orWhere('email', 'like', "%{$this->search}%")
                    )
                    ->orWhere('numero', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->filterEspace, fn($q) => $q->where('espace_id', $this->filterEspace))
            ->when($this->dateDebut, fn($q) => $q->whereDate('debut', '>=', $this->dateDebut))
            ->when($this->dateFin, fn($q) => $q->whereDate('debut', '<=', $this->dateFin))
            ->latest('created_at')
            ->get();

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