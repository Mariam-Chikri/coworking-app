<?php

namespace App\Livewire;

use App\Models\Espace;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class AdminEspaces extends Component
{
    use WithPagination, WithFileUploads;

    // =========================================================
    // Filtres
    // =========================================================
    public string $search       = '';
    public string $filterType   = '';
    public string $filterStatut = '';

    // =========================================================
    // État du modal
    // =========================================================
    public bool    $showModal              = false;
    public bool    $showDeleteModal        = false;
    public ?int    $espaceId               = null;

    /**
     * Chemin de la photo existante en base (STRING).
     * Jamais mis dans $form[] pour éviter les conflits de validation.
     */
    public ?string $photoExistante         = null;

    /**
     * Nouveau fichier uploadé (TemporaryUploadedFile ou null).
     * wire:model="nouvellePhoto" sur l'<input type="file">.
     */
    public $nouvellePhoto = null;

    /** Demande de suppression de la photo existante au prochain save(). */
    public bool $supprimerPhotoExistante   = false;

    /** Données textuelles du formulaire (SANS photo). */
    public array $form = [
        'nom'          => '',
        'type'         => 'bureau_individuel',
        'description'  => '',
        'capacite_min' => 1,
        'capacite_max' => 1,
        'prix_heure'   => 0,
        'prix_journee' => null,
        'prix_mois'    => null,
        'adresse'      => '',
        'actif'        => true,
    ];

    // =========================================================
    // Règles de validation — SANS gte: (bug Livewire sur tableaux imbriqués)
    // =========================================================
    protected function rules(): array
    {
        return [
            'form.nom'          => 'required|string|max:100',
            'form.type'         => 'required|in:bureau_individuel,bureau_prive,open_space,salle_reunion,salle_conference',
            'form.description'  => 'nullable|string',
            'form.capacite_min' => 'required|integer|min:1',
            'form.capacite_max' => 'required|integer|min:1',
            'form.prix_heure'   => 'required|numeric|min:0',
            'form.prix_journee' => 'nullable|numeric|min:0',
            'form.prix_mois'    => 'nullable|numeric|min:0',
            'form.adresse'      => 'nullable|string|max:255',
            'form.actif'        => 'boolean',
            // photo validée séparément uniquement si un nouveau fichier est choisi
            'nouvellePhoto'     => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ];
    }

    protected array $messages = [
        'form.nom.required'        => 'Le nom est obligatoire.',
        'form.capacite_min.min'    => 'La capacité min doit être ≥ 1.',
        'form.capacite_max.min'    => 'La capacité max doit être ≥ 1.',
        'form.prix_heure.required' => 'Le prix/heure est obligatoire.',
        'nouvellePhoto.image'      => 'Le fichier doit être une image.',
        'nouvellePhoto.mimes'      => 'Format accepté : JPG, PNG, WebP.',
        'nouvellePhoto.max'        => 'Taille max : 2 Mo.',
    ];

    // =========================================================
    // Réinitialiser la pagination
    // =========================================================
    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFilterType(): void   { $this->resetPage(); }
    public function updatingFilterStatut(): void { $this->resetPage(); }

    // =========================================================
    // Ouvrir modal CREATE
    // =========================================================
    public function openCreate(): void
    {
        $this->espaceId              = null;
        $this->photoExistante        = null;
        $this->nouvellePhoto         = null;
        $this->supprimerPhotoExistante = false;
        $this->form = [
            'nom'          => '',
            'type'         => 'bureau_individuel',
            'description'  => '',
            'capacite_min' => 1,
            'capacite_max' => 1,
            'prix_heure'   => 0,
            'prix_journee' => null,
            'prix_mois'    => null,
            'adresse'      => '',
            'actif'        => true,
        ];
        $this->resetErrorBag();
        $this->showModal = true;
    }

    // =========================================================
    // Ouvrir modal EDIT
    // =========================================================
    public function openEdit(int $id): void
    {
        $e             = Espace::findOrFail($id);
        $this->espaceId = $id;

        // Données textuelles dans $form (PAS la photo)
        $this->form = [
            'nom'          => $e->nom ?? '',
            'type'         => $e->type ?? 'bureau_individuel',
            'description'  => $e->description ?? '',
            'capacite_min' => $e->capacite_min ?? 1,
            'capacite_max' => $e->capacite_max ?? 1,
            'prix_heure'   => $e->prix_heure ?? 0,
            'prix_journee' => $e->prix_journee,
            'prix_mois'    => $e->prix_mois,
            'adresse'      => $e->adresse ?? '',
            'actif'        => (bool) $e->actif,
        ];

        // Photo existante dans sa propre propriété string
        $this->photoExistante        = $e->photo_principale;
        $this->nouvellePhoto         = null;
        $this->supprimerPhotoExistante = false;

        $this->resetErrorBag();
        $this->showModal = true;
    }

    // =========================================================
    // Bouton "Supprimer la photo"
    // =========================================================
    public function supprimerPhoto(): void
    {
        $this->supprimerPhotoExistante = true;
        $this->photoExistante          = null;
        $this->dispatch('toast', message: 'Photo supprimée (enregistrez pour confirmer).', type: 'info');
    }

    // =========================================================
    // SAUVEGARDER — appelé par wire:click sur le bouton (pas wire:submit)
    // =========================================================
    public function save(): void
    {
        // Validation manuelle de capacite_max >= capacite_min
        if ((int)$this->form['capacite_max'] < (int)$this->form['capacite_min']) {
            $this->addError('form.capacite_max', 'La capacité max doit être ≥ à la capacité min.');
            return;
        }

        $this->validate();

        // Données textuelles
        $data = [
            'nom'          => trim($this->form['nom']),
            'type'         => $this->form['type'],
            'description'  => $this->form['description'],
            'capacite_min' => (int) $this->form['capacite_min'],
            'capacite_max' => (int) $this->form['capacite_max'],
            'prix_heure'   => (float) $this->form['prix_heure'],
            'prix_journee' => $this->form['prix_journee'] ? (float) $this->form['prix_journee'] : null,
            'prix_mois'    => $this->form['prix_mois'] ? (float) $this->form['prix_mois'] : null,
            'adresse'      => $this->form['adresse'],
            'actif'        => (bool) $this->form['actif'],
        ];

        try {
            if ($this->espaceId) {
                // ─── MODIFICATION ─────────────────────────────
                $espace = Espace::findOrFail($this->espaceId);

                // 1. Supprimer la photo existante si demandé
                if ($this->supprimerPhotoExistante && $espace->photo_principale) {
                    Storage::disk('public')->delete($espace->photo_principale);
                    $data['photo_principale'] = null;
                }

                // 2. Remplacer par la nouvelle photo si fournie
                if ($this->nouvellePhoto) {
                    if (!$this->supprimerPhotoExistante && $espace->photo_principale) {
                        Storage::disk('public')->delete($espace->photo_principale);
                    }
                    $data['photo_principale'] = $this->nouvellePhoto->store('espaces', 'public');
                }

                $espace->update($data);
                $this->dispatch('toast', message: 'Espace « ' . $data['nom'] . ' » mis à jour.', type: 'success');

            } else {
                // ─── CRÉATION ─────────────────────────────────
                if ($this->nouvellePhoto) {
                    $data['photo_principale'] = $this->nouvellePhoto->store('espaces', 'public');
                }
                Espace::create($data);
                $this->dispatch('toast', message: 'Espace « ' . $data['nom'] . ' » créé.', type: 'success');
            }

            // Fermer le modal et réinitialiser
            $this->dispatch('$refresh');
            $this->showModal               = false;
            $this->espaceId               = null;
            $this->nouvellePhoto          = null;
            $this->photoExistante         = null;
            $this->supprimerPhotoExistante = false;
            $this->form = [
                'nom' => '', 'type' => 'bureau_individuel', 'description' => '',
                'capacite_min' => 1, 'capacite_max' => 1,
                'prix_heure' => 0, 'prix_journee' => null, 'prix_mois' => null,
                'adresse' => '', 'actif' => true,
            ];
            $this->resetErrorBag();

        } catch (\Exception $e) {
            $this->dispatch('toast', message: 'Erreur : ' . $e->getMessage(), type: 'error');
        }
    }

    // =========================================================
    // Activer / désactiver
    // =========================================================
    public function toggleActif(int $id): void
    {
        $e = Espace::findOrFail($id);
        $e->update(['actif' => !$e->actif]);
        $this->dispatch('toast',
            message: ($e->actif ? 'Espace désactivé' : 'Espace activé') . ' : ' . $e->nom,
            type: 'info'
        );
    }

    // =========================================================
    // Supprimer
    // =========================================================
    public function confirmDelete(int $id): void
    {
        $this->espaceId        = $id;
        $this->showDeleteModal  = true;
    }

    public function delete(): void
    {
        $espace = Espace::findOrFail($this->espaceId);
        if ($espace->photo_principale) {
            Storage::disk('public')->delete($espace->photo_principale);
        }
        $nom = $espace->nom;
        $espace->delete();
        $this->showDeleteModal = false;
        $this->espaceId        = null;
        $this->dispatch('toast', message: 'Espace « ' . $nom . ' » supprimé.', type: 'info');
    }

    // =========================================================
    // Render
    // =========================================================
    public function render()
    {
        $espaces = Espace::query()
            ->when($this->search, fn($q) =>
                // ► GROUPER les OR pour ne pas casser les AND des autres filtres
                $q->where(fn($sub) =>
                    $sub->where('nom', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%")
                        ->orWhere('adresse', 'like', "%{$this->search}%")
                )
            )
            ->when($this->filterType, fn($q) =>
                $q->where('type', $this->filterType)
            )
            ->when($this->filterStatut !== '', fn($q) =>
                $q->where('actif', $this->filterStatut === '1')
            )
            ->withCount('reservations')
            ->orderBy('nom')
            ->paginate(12);

        // Taux d'occupation moyen des espaces actifs ce mois
        $tauxMoyen = Espace::active()->get()
            ->avg(fn($e) => $e->taux_occupation) ?? 0;

        return view('livewire.admin-espaces', compact('espaces', 'tauxMoyen'));
    }
}

