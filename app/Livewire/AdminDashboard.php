<?php

namespace App\Livewire;

use App\Models\Reservation;
use App\Models\Espace;
use App\Models\User;
use App\Models\Facture;
use App\Models\Avis;
use Livewire\Component;
use Illuminate\Support\Facades\DB;

class AdminDashboard extends Component
{
    public array $kpis = [];
    public array $revenusMensuels = [];
    public array $espacesPopulaires = [];
    public array $avisEnAttente = [];
    public string $periodeStats = '30'; // jours

    public function mount()
    {
        $this->chargerStats();
    }

    public function updatedPeriodeStats()
    {
        $this->chargerStats();
    }

    private function chargerStats()
    {
        $debut = now()->subDays((int)$this->periodeStats);

        $this->kpis = [
            'reservations_actives' => Reservation::enCours()->count(),
            'reservations_periode' => Reservation::where('created_at', '>=', $debut)->count(),
            'revenus_periode' => Facture::where('statut', 'payee')->where('date_paiement', '>=', $debut)->sum('montant_ttc'),
            'utilisateurs_total' => User::count(),
            'taux_occupation_global' => $this->tauxOccupationGlobal(),
            'avis_en_attente' => Avis::where('valide', false)->count(),
        ];

        // Revenus 6 mois
        $this->revenusMensuels = [];
        for ($i = 5; $i >= 0; $i--) {
            $mois = now()->subMonths($i);
            $this->revenusMensuels[] = [
                'mois' => $mois->format('M Y'),
                'revenus' => (float) Facture::where('statut', 'payee')
                    ->whereMonth('date_paiement', $mois->month)
                    ->whereYear('date_paiement', $mois->year)
                    ->sum('montant_ttc'),
                'reservations' => Reservation::whereMonth('created_at', $mois->month)
                    ->whereYear('created_at', $mois->year)->count(),
            ];
        }

        // Espaces populaires
        $this->espacesPopulaires = Espace::reservable()
            ->withCount(['reservations as nb_rez' => fn($q) =>
                $q->whereIn('statut', ['confirmee', 'terminee', 'prolongee'])
                  ->where('debut', '>=', $debut)])
            ->orderByDesc('nb_rez')
            ->take(5)
            ->get(['id', 'nom', 'type'])
            ->map(fn($e) => [
                'nom' => $e->nom,
                'type' => $e->type,
                'nb_reservations' => $e->nb_rez,
                'taux_occupation' => $e->taux_occupation,
            ])->toArray();

        // Avis en attente
        $this->avisEnAttente = Avis::where('valide', false)
            ->with(['user:id,name', 'espace:id,nom'])
            ->latest()
            ->take(5)
            ->get()
            ->toArray();
    }

    private function tauxOccupationGlobal(): float
    {
        $espaces = Espace::reservable()->count();
        if (!$espaces) return 0;

        $totalHeuresToccupation = Reservation::whereIn('statut', ['confirmee', 'terminee', 'prolongee'])
            ->whereMonth('debut', now()->month)
            ->join('espaces', 'reservations.espace_id', '=', 'espaces.id')
            ->sum(DB::raw('TIMESTAMPDIFF(HOUR, reservations.debut, reservations.fin)'));

        $totalDisponibles = $espaces * now()->daysInMonth * 10;
        return $totalDisponibles > 0 ? round(($totalHeuresToccupation / $totalDisponibles) * 100, 1) : 0;
    }

    public function validerAvis(int $id)
    {
        Avis::findOrFail($id)->update(['valide' => true]);
        $this->chargerStats();
        $this->dispatch('toast', message: 'Avis validé', type: 'success');
    }

    public function supprimerAvis(int $id)
    {
        Avis::findOrFail($id)->delete();
        $this->chargerStats();
        $this->dispatch('toast', message: 'Avis supprimé', type: 'info');
    }

    public function render()
    {
        $reservationsRecentes = Reservation::with(['user:id,name', 'espace:id,nom'])
            ->latest()->take(10)->get();

        return view('livewire.admin-dashboard', compact('reservationsRecentes'));
    }
}
