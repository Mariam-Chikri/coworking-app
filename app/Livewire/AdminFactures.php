<?php

namespace App\Livewire;

use App\Models\Facture;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class AdminFactures extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatut = '';
    public string $dateDebut = '';
    public string $dateFin = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false; // ✅ AJOUT
    public ?Facture $selected = null;
    public ?int $deleteId = null; // ✅ AJOUT

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatut(): void { $this->resetPage(); }

    public function voir(int $id): void
    {
        $this->selected = Facture::with(['user', 'reservation.espace'])->findOrFail($id);
        $this->showModal = true;
    }

    public function marquerPayee(int $id): void
    {
        $f = Facture::findOrFail($id);
        $f->marquerPayee('virement');
        $this->dispatch('toast', message: 'Facture marquée comme payée', type: 'success');
        if ($this->selected && $this->selected->id === $id) {
            $this->selected = $f->fresh(['user', 'reservation.espace']);
        }
    }

    public function marquerEmise(int $id): void
    {
        Facture::findOrFail($id)->update(['statut' => 'emise', 'date_paiement' => null]);
        $this->dispatch('toast', message: 'Statut remis à "émise"', type: 'info');
    }

    // ✅ AJOUTER CETTE MÉTHODE
    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
        $this->showDeleteModal = true;
    }

    // ✅ AJOUTER CETTE MÉTHODE
    public function delete(): void
    {
        try {
            $facture = Facture::findOrFail($this->deleteId);
            $facture->delete();
            $this->showDeleteModal = false;
            $this->deleteId = null;
            $this->dispatch('toast', message: 'Facture supprimée avec succès.', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Erreur lors de la suppression.', type: 'error');
        }
    }

    public function render()
    {
        $factures = Facture::with(['user:id,name,email', 'reservation:id,numero,espace_id'])
            ->when($this->search, function ($q) {
                $q->where('numero', 'like', "%{$this->search}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%")->orWhere('email', 'like', "%{$this->search}%"));
            })
            ->when($this->filterStatut, fn($q) => $q->where('statut', $this->filterStatut))
            ->when($this->dateDebut, fn($q) => $q->whereDate('date_emission', '>=', $this->dateDebut))
            ->when($this->dateFin, fn($q) => $q->whereDate('date_emission', '<=', $this->dateFin))
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => Facture::count(),
            'payees' => Facture::where('statut', 'payee')->count(),
            'en_attente' => Facture::where('statut', 'emise')->count(),
            'revenus_total' => Facture::where('statut', 'payee')->sum('montant_ttc'),
        ];

        return view('livewire.admin-factures', compact('factures', 'stats'));
    }
}