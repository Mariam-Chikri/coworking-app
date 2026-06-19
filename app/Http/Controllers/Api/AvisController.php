<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Avis;
use App\Models\Espace;
use App\Models\Reservation;
use Illuminate\Http\Request;

class AvisController extends Controller
{
    public function index(Espace $espace)
    {
        $avis = $espace->avis()->with('user:id,name,avatar')->latest()->paginate(10);
        return response()->json($avis);
    }

    public function store(Request $request)
    {
        $request->validate([
            'espace_id' => 'required|exists:espaces,id',
            'reservation_id' => 'required|exists:reservations,id',
            'note' => 'required|integer|between:1,5',
            'titre' => 'nullable|string|max:255',
            'commentaire' => 'nullable|string|max:2000',
        ]);

        $reservation = Reservation::findOrFail($request->reservation_id);
        if ($reservation->user_id !== $request->user()->id) abort(403);
        if ($reservation->espace_id != $request->espace_id) abort(422);
        if (!in_array($reservation->statut, ['terminee'])) {
            return response()->json(['message' => __('messages.avis_reservation_non_terminee')], 422);
        }

        $avis = Avis::updateOrCreate(
            ['user_id' => $request->user()->id, 'reservation_id' => $request->reservation_id],
            [
                'espace_id' => $request->espace_id,
                'note' => $request->note,
                'titre' => $request->titre,
                'commentaire' => $request->commentaire,
                'valide' => false,
            ]
        );

        return response()->json([
            'avis' => $avis,
            'message' => __('messages.avis_soumis'),
        ], 201);
    }

    public function valider(Avis $avis)
    {
        $this->authorize('admin');
        $avis->update(['valide' => true]);
        return response()->json($avis);
    }

    public function destroy(Request $request, Avis $avis)
    {
        if ($avis->user_id !== $request->user()->id && !$request->user()->is_admin) abort(403);
        $avis->delete();
        return response()->json(['message' => __('messages.avis_supprime')]);
    }
}
