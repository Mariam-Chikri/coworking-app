<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Espace;
use App\Models\User;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function dashboard()
    {
        $now = now();

        // KPIs
        $kpis = [
            'reservations_actives' => Reservation::enCours()->count(),
            'reservations_mois' => Reservation::whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year)->count(),
            'revenus_mois' => Facture::where('statut', 'payee')
                ->whereMonth('date_paiement', $now->month)->sum('montant_ttc'),
            'nouveaux_utilisateurs' => User::whereMonth('created_at', $now->month)->count(),
            'taux_annulation' => $this->tauxAnnulation(),
        ];

        // Occupations par espace ce mois
        $occupations = Espace::reservable()
            ->withCount(['reservations as nb_rez_mois' => fn($q) =>
                $q->whereIn('statut', ['confirmee', 'terminee', 'prolongee'])
                  ->whereMonth('debut', $now->month)
                  ->whereYear('debut', $now->year)])
            ->orderByDesc('nb_rez_mois')
            ->get(['id', 'nom', 'type'])
            ->map(fn($e) => [
                'nom' => $e->nom,
                'nb_reservations' => $e->nb_rez_mois,
                'taux_occupation' => $e->taux_occupation,
            ]);

        // Revenus 6 derniers mois
        $revenusMensuels = [];
        for ($i = 5; $i >= 0; $i--) {
            $mois = now()->subMonths($i);
            $revenusMensuels[] = [
                'mois' => $mois->format('M Y'),
                'revenus' => Facture::where('statut', 'payee')
                    ->whereMonth('date_paiement', $mois->month)
                    ->whereYear('date_paiement', $mois->year)
                    ->sum('montant_ttc'),
                'reservations' => Reservation::whereMonth('created_at', $mois->month)
                    ->whereYear('created_at', $mois->year)->count(),
            ];
        }

        // Répartition par type d'espace
        $repartitionTypes = Reservation::whereIn('statut', ['confirmee', 'terminee', 'prolongee'])
            ->join('espaces', 'reservations.espace_id', '=', 'espaces.id')
            ->select('espaces.type', DB::raw('count(*) as total'))
            ->groupBy('espaces.type')
            ->get();

        // Réservations par heure de la journée
        $parHeure = Reservation::whereIn('statut', ['confirmee', 'terminee', 'prolongee'])
            ->selectRaw('HOUR(debut) as heure, count(*) as total')
            ->groupBy('heure')
            ->orderBy('heure')
            ->get();

        return response()->json([
            'kpis' => $kpis,
            'espaces_populaires' => $occupations,
            'revenus_mensuels' => $revenusMensuels,
            'repartition_types' => $repartitionTypes,
            'reservations_par_heure' => $parHeure,
        ]);
    }

    private function tauxAnnulation(): float
    {
        $total = Reservation::whereMonth('created_at', now()->month)->count();
        if (!$total) return 0;
        $annulees = Reservation::whereMonth('created_at', now()->month)
            ->where('statut', 'annulee')->count();
        return round(($annulees / $total) * 100, 1);
    }

    public function espaceStats(Request $request)
    {
        $espaces = Espace::reservable()->with(['avis'])->get()->map(fn($e) => [
            'id' => $e->id,
            'nom' => $e->nom,
            'type' => $e->type,
            'capacite' => $e->capacite,
            'prix_heure' => $e->prix_heure,
            'taux_occupation' => $e->taux_occupation,
            'note_moyenne' => $e->notes_moyenne,
            'nb_reservations_total' => $e->reservations()->whereIn('statut', ['confirmee', 'terminee', 'prolongee'])->count(),
            'revenus_total' => $e->reservations()->whereIn('statut', ['confirmee', 'terminee', 'prolongee'])->sum('prix_total'),
        ]);

        return response()->json($espaces);
    }
}
