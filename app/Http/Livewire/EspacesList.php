<?php

namespace App\Http\Livewire;

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
            ->withAvg('avis', 'note')
            ->withCount('avis');

        if ($this->recherche) {
            $query->where(fn($q) =>
                $q->where('nom', 'like', "%{$this->recherche}%")
                  ->orWhere('description', 'like', "%{$this->recherche}%")
            );
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->capaciteMin > 1) {
            $query->where('capacite', '>=', $this->capaciteMin);
        }

        if ($this->debut && $this->fin) {
            $query->disponible($this->debut, $this->fin);
        }

        $espaces = $query->orderBy($this->tri)->paginate(9);
        $espacesNonReservables = Espace::nonReservable()->get();

        $favorisIds = auth()->check()
            ? auth()->user()->espacesFavoris()->pluck('espaces.id')->toArray()
            : [];

        return view('livewire.espaces-list', compact('espaces', 'espacesNonReservables', 'favorisIds'));
    }

    public function toggleFavori(int $espaceId)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
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

    public function resetFiltres()
    {
        $this->reset(['recherche', 'type', 'capaciteMin', 'debut', 'fin']);
        $this->resetPage();
    }
}
