<?php

namespace App\Livewire;

use App\Models\Avis;
use App\Models\Espace;
use Livewire\Component;
use Livewire\WithPagination;

class AdminAvis extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatut = '';
    public string $filterEspace = '';
    public string $filterNote = '';

    public bool $showDeleteModal = false;
    public ?int $avisId = null;

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterStatut(): void { $this->resetPage(); }
    public function updatingFilterEspace(): void { $this->resetPage(); }
    public function updatingFilterNote(): void { $this->resetPage(); }

    public function valider(int $id): void
    {
        Avis::findOrFail($id)->update(['valide' => true]);
        $this->dispatch('toast', message: 'Avis validé et publié', type: 'success');
    }

    public function rejeter(int $id): void
    {
        Avis::findOrFail($id)->update(['valide' => false]);
        $this->dispatch('toast', message: 'Avis rejeté', type: 'info');
    }

    public function confirmDelete(int $id): void
    {
        $this->avisId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        Avis::findOrFail($this->avisId)->delete();
        $this->showDeleteModal = false;
        $this->avisId = null;
        $this->dispatch('toast', message: 'Avis supprimé', type: 'info');
    }

    public function render()
    {
        $avis = Avis::with(['user:id,name,email', 'espace:id,nom'])
            ->when($this->search, function ($q) {
                $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%"))
                  ->orWhere('commentaire', 'like', "%{$this->search}%")
                  ->orWhere('titre', 'like', "%{$this->search}%");
            })
            ->when($this->filterStatut === 'valide', fn($q) => $q->where('valide', true))
            ->when($this->filterStatut === 'en_attente', fn($q) => $q->where('valide', false))
            ->when($this->filterEspace, fn($q) => $q->where('espace_id', $this->filterEspace))
            ->when($this->filterNote, fn($q) => $q->where('note', $this->filterNote))
            ->latest()
            ->paginate(15);

        $espaces = Espace::orderBy('nom')->get(['id', 'nom']);

        $stats = [
            'total' => Avis::count(),
            'en_attente' => Avis::where('valide', false)->count(),
            'valides' => Avis::where('valide', true)->count(),
            'moyenne' => round(Avis::avg('note'), 1),
        ];

        return view('livewire.admin-avis', compact('avis', 'espaces', 'stats'));
    }
}
