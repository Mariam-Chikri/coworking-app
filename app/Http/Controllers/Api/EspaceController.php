<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Espace;
use Illuminate\Http\Request;

class EspaceController extends Controller
{
    public function index(Request $request)
    {
        $query = Espace::query()->where('actif', true);

        if ($request->boolean('reservable')) {
            $query->where('reservable', true);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('capacite_min')) {
            $query->where('capacite', '>=', $request->capacite_min);
        }

        if ($request->filled('debut') && $request->filled('fin')) {
            $query->disponible($request->debut, $request->fin);
        }

        $espaces = $query->withCount(['reservations', 'avis'])
            ->withAvg('avis', 'note')
            ->get()
            ->map(function ($espace) use ($request) {
                $espace->est_favori = $request->user()
                    ? $request->user()->espacesFavoris->contains($espace->id)
                    : false;
                return $espace;
            });

        return response()->json($espaces);
    }

    public function show(Request $request, Espace $espace)
    {
        $espace->load(['avis.user']);
        $espace->est_favori = $request->user()
            ? $request->user()->espacesFavoris->contains($espace->id)
            : false;

        return response()->json($espace);
    }

    public function disponibilite(Request $request, Espace $espace)
    {
        $request->validate([
            'debut' => 'required|date',
            'fin' => 'required|date|after:debut',
        ]);

        $conflit = \App\Models\Reservation::where('espace_id', $espace->id)
            ->whereNotIn('statut', ['annulee'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('debut', [$request->debut, $request->fin])
                  ->orWhereBetween('fin', [$request->debut, $request->fin])
                  ->orWhere(function ($q2) use ($request) {
                      $q2->where('debut', '<=', $request->debut)
                         ->where('fin', '>=', $request->fin);
                  });
            })->exists();

        return response()->json(['disponible' => !$conflit]);
    }

    public function stats(Espace $espace)
    {
        $moisActuel = now();
        $reservationsMois = $espace->reservations()
            ->whereIn('statut', ['confirmee', 'terminee', 'prolongee'])
            ->whereYear('debut', $moisActuel->year)
            ->whereMonth('debut', $moisActuel->month)
            ->count();

        $heuresTotales = $espace->reservations()
            ->whereIn('statut', ['confirmee', 'terminee', 'prolongee'])
            ->whereYear('debut', $moisActuel->year)
            ->whereMonth('debut', $moisActuel->month)
            ->get()
            ->sum('duree_heures');

        return response()->json([
            'reservations_mois' => $reservationsMois,
            'heures_totales' => round($heuresTotales, 1),
            'taux_occupation' => $espace->taux_occupation,
            'note_moyenne' => $espace->notes_moyenne,
            'nb_avis' => $espace->avis()->count(),
        ]);
    }

    // Admin
    public function store(Request $request)
    {
        $this->authorize('admin');
        $request->validate([
            'nom' => 'required|string|max:255',
            'capacite' => 'required|integer|min:1',
            'prix_heure' => 'required|numeric|min:0',
            'type' => 'required|in:bureau,salle,open_space,non_reservable',
        ]);

        $espace = Espace::create($request->all());
        return response()->json($espace, 201);
    }

    public function update(Request $request, Espace $espace)
    {
        $this->authorize('admin');
        $espace->update($request->all());
        return response()->json($espace);
    }

    public function destroy(Espace $espace)
    {
        $this->authorize('admin');
        $espace->update(['actif' => false]);
        return response()->json(['message' => 'Espace désactivé']);
    }
}
