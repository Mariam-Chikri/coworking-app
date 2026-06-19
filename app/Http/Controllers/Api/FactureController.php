<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class FactureController extends Controller
{
    public function index(Request $request)
    {
        $factures = $request->user()->factures()
            ->with('reservation.espace')
            ->latest()
            ->paginate(15);

        return response()->json($factures);
    }

    public function show(Request $request, Facture $facture)
    {
        if ($facture->user_id !== $request->user()->id && !$request->user()->is_admin) {
            abort(403);
        }

        return response()->json($facture->load('reservation.espace', 'user'));
    }

    public function telecharger(Request $request, Facture $facture)
    {
        if ($facture->user_id !== $request->user()->id && !$request->user()->is_admin) {
            abort(403);
        }

        $facture->load('reservation.espace', 'user');

        $pdf = Pdf::loadView('factures.pdf', compact('facture'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('facture-' . $facture->numero . '.pdf');
    }

    public function marquerPayee(Request $request, Facture $facture)
    {
        $this->authorize('admin');
        $request->validate(['methode' => 'required|in:virement,carte,especes,cheque']);
        $facture->marquerPayee($request->methode);
        return response()->json($facture);
    }
}
