<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

/**
 * Composant Livewire pour la gestion du profil utilisateur.
 * Remplace les formulaires HTML classiques → zéro rechargement de page.
 */
class ProfileEdit extends Component
{
    // ── Onglet actif ───────────────────────────────────────────
    public string $tab = 'profil';

    // ── Données du profil ────────────────────────────────────────
    public string $name       = '';
    public string $email      = '';
    public string $telephone  = '';
    public string $entreprise = '';

    // ── Changement de mot de passe ───────────────────────────────
    public string $current_password = '';
    public string $password         = '';
    public string $password_confirmation = '';

    // ── Suppression du compte ────────────────────────────────────
    public bool   $showDeleteModal   = false;
    public string $delete_password   = '';

    // ════════════════════════════════════════════════════════════

    public function mount(): void
    {
        $user = auth()->user();
        $this->name       = $user->name;
        $this->email      = $user->email;
        $this->telephone  = $user->telephone ?? '';
        $this->entreprise = $user->entreprise ?? '';
    }

    // ── Mettre à jour les informations du profil ─────────────────
    public function updateProfile(): void
    {
        $user = auth()->user();

        $this->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email,' . $user->id,
            'telephone'  => 'nullable|string|max:30',
            'entreprise' => 'nullable|string|max:255',
        ], [
            'name.required'  => 'Le nom est obligatoire.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.unique'   => 'Cette adresse email est déjà utilisée.',
        ]);

        $user->update([
            'name'       => trim($this->name),
            'email'      => trim($this->email),
            'telephone'  => $this->telephone ?: null,
            'entreprise' => $this->entreprise ?: null,
        ]);

        $this->dispatch('toast', message: 'Profil mis à jour avec succès.', type: 'success');
    }

    // ── Mettre à jour le mot de passe ────────────────────────────
    public function updatePassword(): void
    {
        $this->validate([
            'current_password'      => 'required|current_password',
            'password'              => ['required', 'confirmed', Password::min(8)],
            'password_confirmation' => 'required',
        ], [
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.min'                      => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'                => 'Les mots de passe ne correspondent pas.',
        ]);

        auth()->user()->update([
            'password' => Hash::make($this->password),
        ]);

        $this->current_password      = '';
        $this->password              = '';
        $this->password_confirmation = '';

        $this->dispatch('toast', message: 'Mot de passe mis à jour.', type: 'success');
    }

    // ── Supprimer le compte ───────────────────────────────────────
    public function deleteAccount(): void
    {
        $this->validate([
            'delete_password' => 'required|current_password',
        ], [
            'delete_password.current_password' => 'Mot de passe incorrect.',
        ]);

        $user = auth()->user();
        Auth::logout();
        $user->delete();

        session()->invalidate();
        session()->regenerateToken();

        $this->redirect('/');
    }

    // ════════════════════════════════════════════════════════════

    public function render()
    {
        $user = auth()->user();

        // Statistiques du tableau de bord profil
        $stats = [
            'total_rez'   => $user->reservations()->count(),
            'actives'     => $user->reservations()->whereIn('statut', ['confirmee', 'prolongee'])->count(),
            'depenses'    => $user->factures()->where('statut', 'payee')->sum('montant_ttc'),
        ];

        return view('livewire.profile-edit', compact('stats'));
    }
}

