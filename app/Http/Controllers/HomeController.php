<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // ✅ Récupérer les chemins des images depuis la config
        $aboutImages = config('about_images', [
            'cafe' => null,
            'terrasse' => null,
            'salon' => null,
        ]);

        return view('home', compact('aboutImages'));
    }
}