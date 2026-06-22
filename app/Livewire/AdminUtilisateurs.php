<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Hash;

class AdminUtilisateurs extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterRole = '';

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $userId = null;

    public array $form = [
        'name' => '', 'email' => '', 'password' => '',
        'telephone' => '', 'entreprise' => '',
        'is_admin' => false,
    ];

    protected function rules(): array
    {
        return [
            'form.name'       => 'required|string|max:100',
            'form.email'      => 'required|email|unique:users,email' . ($this->userId ? ",{$this->userId}" : ''),
            'form.password'   => $this->userId ? 'nullable|min:8' : 'required|min:8',
            'form.telephone'  => 'nullable|string|max:20',
            'form.entreprise' => 'nullable|string|max:100',
            'form.is_admin'   => 'boolean',
        ];
    }

    public function updatingSearch(): void { $this->resetPage(); }
    public function updatingFilterRole(): void { $this->resetPage(); }

    public function openCreate(): void
    {
        $this->reset('userId');
        $this->form = ['name' => '', 'email' => '', 'password' => '', 'telephone' => '', 'entreprise' => '', 'is_admin' => false];
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $u = User::findOrFail($id);
        $this->userId = $id;
        $this->form = [
            'name' => $u->name, 'email' => $u->email, 'password' => '',
            'telephone' => $u->telephone ?? '', 'entreprise' => $u->entreprise ?? '',
            'is_admin' => (bool) $u->is_admin,
        ];
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = collect($this->form)->except('password')->toArray();
        if (!empty($this->form['password'])) {
            $data['password'] = Hash::make($this->form['password']);
        }

        if ($this->userId) {
            User::findOrFail($this->userId)->update($data);
            $this->dispatch('toast', message: 'Utilisateur mis à jour', type: 'success');
        } else {
            $data['password'] = Hash::make($this->form['password']);
            User::create($data);
            $this->dispatch('toast', message: 'Utilisateur créé', type: 'success');
        }

        $this->showModal = false;
        $this->reset('userId', 'form');
    }

    public function toggleAdmin(int $id): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('toast', message: 'Impossible de modifier votre propre rôle', type: 'error');
            return;
        }
        $u = User::findOrFail($id);
        $u->update(['is_admin' => !$u->is_admin]);
        $this->dispatch('toast', message: $u->is_admin ? 'Admin accordé' : 'Admin révoqué', type: 'info');
    }

    public function confirmDelete(int $id): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('toast', message: 'Impossible de supprimer votre propre compte', type: 'error');
            return;
        }
        $this->userId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        User::findOrFail($this->userId)->delete();
        $this->showDeleteModal = false;
        $this->userId = null;
        $this->dispatch('toast', message: 'Utilisateur supprimé', type: 'info');
    }

    public function render()
    {
        $utilisateurs = User::withCount(['reservations', 'factures'])
            ->when($this->search, fn($q) =>
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('email', 'like', "%{$this->search}%")
                  ->orWhere('entreprise', 'like', "%{$this->search}%")
            )
            ->when($this->filterRole === 'admin', fn($q) => $q->where('is_admin', true))
            ->when($this->filterRole === 'user', fn($q) => $q->where('is_admin', false))
            ->latest()
            ->paginate(15);

        $stats = [
            'total' => User::count(),
            'admins' => User::where('is_admin', true)->count(),
            'nouveaux' => User::where('created_at', '>=', now()->subDays(30))->count(),
        ];

        return view('livewire.admin-utilisateurs', compact('utilisateurs', 'stats'));
    }
}
