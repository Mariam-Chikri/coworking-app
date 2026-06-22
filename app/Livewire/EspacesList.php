<?php

namespace App\Livewire;

use App\Models\Espace;
use Livewire\Component;
use Livewire\WithPagination;

class EspacesList extends Component
{
    use WithPagination;

    public string $recherche = '';
    public string $type = '';
    public int $capaciteMin = 1;
    public string $debut = '';
    public string $fin = '';
    public string $tri = 'nom';

    protected $queryString = ['recherche', 'type', 'capaciteMin', 'debut', 'fin'];

    public function render()
    {
        $query = Espace::reservable()
            ->withAvg(['avis' => fn($q) => $q->where('valide', true)], 'note')
            ->withCount(['avis' => fn($q) => $q->where('valide', true)]);

        if ($this->recherche) {
            $query->where(fn($q) =>
                $q->where('nom', 'like', "%{$this->recherche}%")
                  ->orWhere('description', 'like', "%{$this->recherche}%")
            );
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        // Filtrer par capacité maximale (l'espace doit pouvoir accueillir au moins N personnes)
        if ($this->capaciteMin > 1) {
            $query->where('capacite_max', '>=', $this->capaciteMin);
        }

        if ($this->debut && $this->fin) {
            $query->disponible($this->debut, $this->fin);
        }

        $espaces = $query->orderBy($this->tri)->paginate(9);

        $favorisIds = auth()->check()
            ? auth()->user()->favoris()->pluck('espace_id')->toArray()
            : [];

        return view('livewire.espaces-list', compact('espaces', 'favorisIds'));
    }

    public function toggleFavori(int $espaceId): void
    {
        if (!auth()->check()) {
            $this->redirect(route('login'));
            return;
        }

        $user = auth()->user();
        $existant = $user->favoris()->where('espace_id', $espaceId)->first();

        if ($existant) {
            $existant->delete();
            $this->dispatch('toast', message: __('messages.favori_supprime'), type: 'info');
        } else {
            $user->favoris()->create(['espace_id' => $espaceId]);
            $this->dispatch('toast', message: __('messages.favori_ajoute'), type: 'success');
        }
    }

    public function resetFiltres(): void
    {
        $this->reset(['recherche', 'type', 'capaciteMin', 'debut', 'fin']);
        $this->resetPage();
    }
}

