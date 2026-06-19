<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Espace;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->reservations()
            ->with(['espace', 'facture'])
            ->latest();

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        return response()->json($query->paginate(15));
    }

    public function store(Request $request)
    {
        $request->validate([
            'espace_id' => 'required|exists:espaces,id',
            'debut' => 'required|date|after:now',
            'fin' => 'required|date|after:debut',
            'nombre_personnes' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $espace = Espace::findOrFail($request->espace_id);

        if (!$espace->reservable) {
            return response()->json(['message' => __('messages.espace_non_reservable')], 422);
        }

        if ($request->nombre_personnes > $espace->capacite) {
            return response()->json(['message' => __('messages.capacite_depassee')], 422);
        }

        // Vérifier disponibilité
        $conflit = Reservation::where('espace_id', $espace->id)
            ->whereNotIn('statut', ['annulee'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('debut', [$request->debut, $request->fin])
                  ->orWhereBetween('fin', [$request->debut, $request->fin])
                  ->orWhere(fn($q2) => $q2->where('debut', '<=', $request->debut)->where('fin', '>=', $request->fin));
            })->exists();

        if ($conflit) {
            return response()->json(['message' => __('messages.creneau_indisponible')], 422);
        }

        return DB::transaction(function () use ($request, $espace) {
            $debut = new \DateTime($request->debut);
            $fin = new \DateTime($request->fin);
            $heures = ($fin->getTimestamp() - $debut->getTimestamp()) / 3600;
            $prixTotal = round($heures * $espace->prix_heure, 2);

            $reservation = Reservation::create([
                'user_id' => $request->user()->id,
                'espace_id' => $espace->id,
                'debut' => $request->debut,
                'fin' => $request->fin,
                'statut' => 'confirmee',
                'prix_total' => $prixTotal,
                'notes' => $request->notes,
                'nombre_personnes' => $request->nombre_personnes,
            ]);

            $facture = Facture::creerPourReservation($reservation);
            $reservation->load(['espace', 'facture']);

            return response()->json([
                'reservation' => $reservation,
                'message' => __('messages.reservation_confirmee'),
            ], 201);
        });
    }

    public function show(Request $request, Reservation $reservation)
    {
        $this->authorize('view', $reservation);
        return response()->json($reservation->load(['espace', 'facture', 'avis', 'user']));
    }

    public function prolonger(Request $request, Reservation $reservation)
    {
        $this->authorize('view', $reservation);
        $request->validate(['heures' => 'required|integer|min:1|max:8']);

        if (!$reservation->canBeProlonged()) {
            return response()->json(['message' => __('messages.prolongation_impossible')], 422);
        }

        try {
            $reservation->prolonger($request->heures);
            return response()->json([
                'reservation' => $reservation->fresh(['espace', 'facture']),
                'message' => __('messages.prolongation_reussie'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function libererAnticipement(Request $request, Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        if (!$reservation->canBeReleasedEarly()) {
            return response()->json(['message' => __('messages.liberation_impossible')], 422);
        }

        $reservation->libererAnticipement();
        return response()->json([
            'reservation' => $reservation->fresh(['espace', 'facture']),
            'message' => __('messages.liberation_reussie'),
        ]);
    }

    public function annuler(Request $request, Reservation $reservation)
    {
        $this->authorize('view', $reservation);

        if (!in_array($reservation->statut, ['en_attente', 'confirmee'])) {
            return response()->json(['message' => __('messages.annulation_impossible')], 422);
        }

        if ($reservation->debut->isPast()) {
            return response()->json(['message' => __('messages.annulation_passee')], 422);
        }

        $reservation->update(['statut' => 'annulee']);
        return response()->json(['message' => __('messages.reservation_annulee')]);
    }

    // Admin
    public function adminIndex(Request $request)
    {
        $this->authorize('admin');
        $query = Reservation::with(['user', 'espace', 'facture'])->latest();

        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('espace_id')) $query->where('espace_id', $request->espace_id);

        return response()->json($query->paginate(20));
    }

    public function adminConfirmer(Reservation $reservation)
    {
        $this->authorize('admin');
        $reservation->update(['statut' => 'confirmee']);
        return response()->json($reservation->fresh());
    }
}
