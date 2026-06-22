<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Afficher le formulaire de profil (Breeze default — redirige vers notre vue personnalisée).
     */
    public function edit(Request $request): RedirectResponse
    {
        return redirect()->route('profile');
    }

    /**
     * Mettre à jour les infos du profil (nom, email, téléphone, entreprise).
     */
    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:255'],
            'email'      => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'telephone'  => ['nullable', 'string', 'max:30'],
            'entreprise' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($validated);

        if ($request->expectsJson()) {
            return back()->with('status', 'profile-updated');
        }

        return redirect()->route('profile')
            ->with('success', app()->getLocale() === 'en'
                ? 'Profile updated successfully!'
                : 'Profil mis à jour avec succès !');
    }

    /**
     * Mettre à jour le mot de passe.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'confirmed', Password::min(8)],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('profile')
            ->with('success', app()->getLocale() === 'en'
                ? 'Password updated successfully!'
                : 'Mot de passe mis à jour avec succès !');
    }

    /**
     * Supprimer le compte (Breeze default).
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = Auth::user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}

