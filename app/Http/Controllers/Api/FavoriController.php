<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favori;
use App\Models\Espace;
use Illuminate\Http\Request;

class FavoriController extends Controller
{
    public function index(Request $request)
    {
        $favoris = $request->user()->espacesFavoris()
            ->withAvg('avis', 'note')
            ->withCount('avis')
            ->get();

        return response()->json($favoris);
    }

    public function toggle(Request $request, Espace $espace)
    {
        $user = $request->user();
        $existant = Favori::where('user_id', $user->id)
            ->where('espace_id', $espace->id)->first();

        if ($existant) {
            $existant->delete();
            return response()->json(['action' => 'removed', 'message' => __('messages.favori_supprime')]);
        }

        Favori::create(['user_id' => $user->id, 'espace_id' => $espace->id]);
        return response()->json(['action' => 'added', 'message' => __('messages.favori_ajoute')]);
    }
}
