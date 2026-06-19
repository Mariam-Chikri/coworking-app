<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'telephone'  => ['nullable', 'string', 'max:30'],
            'entreprise' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($validated);

        return redirect()->route('profile', ['tab' => 'profil'])
            ->with('success', app()->getLocale() === 'en'
                ? 'Profile updated successfully!'
                : 'Profil mis à jour avec succès !');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile', ['tab' => 'profil'])
            ->with('success', app()->getLocale() === 'en'
                ? 'Password updated successfully!'
                : 'Mot de passe mis à jour avec succès !');
    }
}
