<?php

namespace App\Livewire;

use App\Models\Espace;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminEspaces extends Component
{
    use WithPagination, WithFileUploads;

    // =========================================================
    // Filtres de liste
    // =========================================================
    public string $search       = '';
    public string $filterType   = '';
    public string $filterStatut = '';

    // =========================================================
    // État du modal
    // =========================================================
    public bool $showModal       = false;
    public bool $showDeleteModal = false;
    public ?int $espaceId        = null;

    // =========================================================
    // Champs du formulaire — propriétés INDIVIDUELLES
    // =========================================================
    public string $fNom          = '';
    public string $fType         = 'bureau_individuel';
    public string $fDescription  = '';
    public string $fCapaciteMin  = '1';
    public string $fCapaciteMax  = '1';
    public string $fPrixHeure    = '0';
    public string $fPrixJournee  = '';
    public string $fPrixMois     = '';
    public string $fNombreBureaux = '0';
    public bool   $fActif        = true;

    // Photo
    public ?string $photoExistante     = null;
    public $nouvellePhoto              = null;
    public bool   $supprimerPhotoFlag  = false;

    // =========================================================
    // Types d'espaces disponibles
    // =========================================================
    protected array $types = [
        'bureau_individuel' => 'Bureau individuel',
        'bureau_prive' => 'Bureau privé',
        'open_space_creatif' => 'Open space créatif',
        'salle_reunion' => 'Salle de réunion',
        'salle_conference' => 'Salle de conférence',
        'non_reservable' => 'Non réservable',
    ];

    // =========================================================
    // Réinitialisation de la pagination sur filtre
    // =========================================================
    public function updatingSearch(): void       { $this->resetPage(); }
    public function updatingFilterType(): void   { $this->resetPage(); }
    public function updatingFilterStatut(): void { $this->resetPage(); }

    // =========================================================
    // Réinitialiser les champs
    // =========================================================
    private function resetFields(): void
    {
        $this->fNom          = '';
        $this->fType         = 'bureau_individuel';
        $this->fDescription  = '';
        $this->fCapaciteMin  = '1';
        $this->fCapaciteMax  = '1';
        $this->fPrixHeure    = '0';
        $this->fPrixJournee  = '';
        $this->fPrixMois     = '';
        $this->fNombreBureaux = '0';
        $this->fActif        = true;
        $this->photoExistante    = null;
        $this->nouvellePhoto     = null;
        $this->supprimerPhotoFlag = false;

        $this->resetErrorBag();
    }
    
    public function updatedFType($value)
    {
        // Réinitialiser le nombre de bureaux si ce n'est pas un open_space_creatif
        if ($value !== 'open_space_creatif') {
            $this->fNombreBureaux = '0';
        }
        
        // Si le type est non_reservable, réinitialiser les prix
        if ($value === 'non_reservable') {
            $this->fPrixHeure = '0';
            $this->fPrixJournee = '';
            $this->fPrixMois = '';
        }
    }

    // =========================================================
    // Ouvrir modal CRÉER
    // =========================================================
    public function openCreate(): void
    {
        $this->espaceId = null;
        $this->resetFields();
        $this->fNombreBureaux = '0';
        $this->showModal = true;
    }

    // =========================================================
    // Ouvrir modal MODIFIER
    // =========================================================
    public function openEdit(int $id): void
    {
        $e = Espace::findOrFail($id);

        $this->espaceId      = $id;
        $this->fNom          = (string) ($e->nom ?? '');
        $this->fType         = (string) ($e->type ?? 'bureau_individuel');
        $this->fDescription  = (string) ($e->description ?? '');
        $this->fCapaciteMin  = (string) ($e->capacite_min ?? '1');
        $this->fCapaciteMax  = (string) ($e->capacite_max ?? '1');
        $this->fPrixHeure    = (string) ($e->prix_heure ?? '0');
        $this->fPrixJournee  = $e->prix_journee ? (string) $e->prix_journee : '';
        $this->fPrixMois     = $e->prix_mois    ? (string) $e->prix_mois    : '';
        $this->fNombreBureaux = (string) ($e->nombre_bureaux ?? '0');
        $this->fActif        = (bool) $e->actif;

        $this->photoExistante    = $e->photo_principale;
        $this->nouvellePhoto     = null;
        $this->supprimerPhotoFlag = false;

        $this->resetErrorBag();
        $this->showModal = true;
    }

    // =========================================================
    // Demander suppression de la photo existante
    // =========================================================
    public function supprimerPhoto(): void
    {
        $this->supprimerPhotoFlag = true;
        $this->photoExistante     = null;
    }

    // =========================================================
    // Récupérer les colonnes disponibles de la table espaces
    // =========================================================
    private function getAvailableColumns(): array
    {
        static $columns = null;
        if ($columns === null) {
            try {
                $columns = \Schema::getColumnListing('espaces');
            } catch (\Throwable $e) {
                $columns = [];
            }
        }
        return $columns;
    }

    // =========================================================
    // SAUVEGARDER
    // =========================================================
    public function save(): void
    {
        // ✅ Validation conditionnelle selon le type
        $rules = [
            'fNom' => 'required|string|max:100',
            'fType' => 'required|in:' . implode(',', array_keys($this->types)),
            'fCapaciteMin' => 'required|integer|min:1',
            'fCapaciteMax' => 'required|integer|min:1',
            'fPrixJournee' => 'nullable|numeric|min:0',
            'fPrixMois' => 'nullable|numeric|min:0',
            'fNombreBureaux' => 'nullable|integer|min:0',
            'nouvellePhoto' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:20480',
        ];

        // ✅ Prix/heure obligatoire sauf pour non_reservable
        if ($this->fType !== 'non_reservable') {
            $rules['fPrixHeure'] = 'required|numeric|min:0';
        } else {
            $rules['fPrixHeure'] = 'nullable|numeric|min:0';
        }

        // ✅ Nombre de bureaux obligatoire pour open_space_creatif
        if ($this->fType === 'open_space_creatif') {
            $rules['fNombreBureaux'] = 'required|integer|min:5|max:500';
        }

        $this->validate($rules, [
            'fNom.required' => 'Le nom est obligatoire.',
            'fNombreBureaux.required' => 'Le nombre de bureaux est obligatoire pour les open spaces créatifs.',
            'fNombreBureaux.min' => 'Le nombre de bureaux doit être au minimum 5.',
            'fNombreBureaux.max' => 'Le nombre de bureaux ne peut pas dépasser 500.',
            'fCapaciteMin.min' => 'La capacité min doit être ≥ 1.',
            'fCapaciteMax.min' => 'La capacité max doit être ≥ 1.',
            'fPrixHeure.required' => 'Le prix/heure est obligatoire pour les espaces réservables.',
            'nouvellePhoto.image' => 'Le fichier doit être une image.',
            'nouvellePhoto.mimes' => 'Format accepté : JPG, PNG, WebP.',
            'nouvellePhoto.max' => 'Taille max : 20 Mo.',
        ]);

        // ✅ Vérification capacite_max >= capacite_min
        if ((int)$this->fCapaciteMax < (int)$this->fCapaciteMin) {
            $this->addError('fCapaciteMax', 'La capacité max doit être ≥ à la capacité min.');
            return;
        }

        // ✅ Préparer les données
        $data = [
            'nom'         => trim($this->fNom),
            'type'        => $this->fType,
            'description' => trim($this->fDescription) ?: null,
            'actif'       => $this->fActif,
        ];

        $columns = $this->getAvailableColumns();

        if (in_array('capacite_min', $columns)) {
            $data['capacite_min'] = (int) $this->fCapaciteMin;
        }
        if (in_array('capacite_max', $columns)) {
            $data['capacite_max'] = (int) $this->fCapaciteMax;
        } elseif (in_array('capacite', $columns)) {
            $data['capacite'] = (int) $this->fCapaciteMax;
        }
        if (in_array('prix_heure', $columns)) {
            $data['prix_heure'] = $this->fType === 'non_reservable' ? 0 : (float) $this->fPrixHeure;
        }
        if (in_array('prix_journee', $columns)) {
            $data['prix_journee'] = $this->fType === 'non_reservable' ? null : ($this->fPrixJournee !== '' ? (float) $this->fPrixJournee : null);
        }
        if (in_array('prix_mois', $columns)) {
            $data['prix_mois'] = $this->fType === 'non_reservable' ? null : ($this->fPrixMois !== '' ? (float) $this->fPrixMois : null);
        }
        if ($this->fType === 'open_space_creatif' && in_array('nombre_bureaux', $columns)) {
            $data['nombre_bureaux'] = (int) $this->fNombreBureaux;
        } elseif (in_array('nombre_bureaux', $columns)) {
            $data['nombre_bureaux'] = null;
        }

        try {
            if ($this->espaceId) {
                // ─── MODIFICATION ─────────────────────────────────────────
                $espace = Espace::findOrFail($this->espaceId);

                // Gestion photo : suppression de l'ancienne
                if ($this->supprimerPhotoFlag && $espace->photo_principale) {
                    Storage::disk('public')->delete($espace->photo_principale);
                    if (in_array('photo_principale', $columns)) {
                        $data['photo_principale'] = null;
                    }
                }

                // Gestion photo : nouvelle photo uploadée
                if ($this->nouvellePhoto) {
                    if (!$this->supprimerPhotoFlag && $espace->photo_principale) {
                        Storage::disk('public')->delete($espace->photo_principale);
                    }
                    if (in_array('photo_principale', $columns)) {
                        $data['photo_principale'] = $this->nouvellePhoto->store('espaces', 'public');
                    }
                }

                $espace->update($data);

                $this->dispatch('toast', message: 'Espace « ' . $data['nom'] . ' » mis à jour avec succès.', type: 'success');

            } else {
                // ─── CRÉATION ─────────────────────────────────────────────
                if ($this->nouvellePhoto && in_array('photo_principale', $columns)) {
                    $data['photo_principale'] = $this->nouvellePhoto->store('espaces', 'public');
                }

                Espace::create($data);

                $this->dispatch('toast', message: 'Espace « ' . $data['nom'] . ' » créé avec succès.', type: 'success');
            }

            // Fermer le modal et réinitialiser
            $this->showModal = false;
            $this->espaceId  = null;
            $this->resetFields();

        } catch (\Throwable $e) {
            Log::error('AdminEspaces::save error', [
                'message' => $e->getMessage(),
                'data'    => $data,
            ]);
            $this->dispatch('toast', message: 'Erreur : ' . $e->getMessage(), type: 'error');
        }
    }

    // =========================================================
    // Obtenir le libellé du type d'espace
    // =========================================================
    public function getTypeLabel(string $type): string
    {
        return $this->types[$type] ?? $type;
    }

    // =========================================================
    // Activer / désactiver
    // =========================================================
    public function toggleActif(int $id): void
    {
        $e = Espace::findOrFail($id);
        $e->update(['actif' => !$e->actif]);
        $this->dispatch('toast',
            message: ($e->fresh()->actif ? 'Espace activé' : 'Espace désactivé') . ' : ' . $e->nom,
            type: 'info'
        );
    }

    // =========================================================
    // Supprimer
    // =========================================================
    public function confirmDelete(int $id): void
    {
        $this->espaceId       = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        try {
            $espace = Espace::findOrFail($this->espaceId);
            if ($espace->photo_principale) {
                Storage::disk('public')->delete($espace->photo_principale);
            }
            $nom = $espace->nom;
            $espace->delete();
            $this->showDeleteModal = false;
            $this->espaceId       = null;
            $this->dispatch('toast', message: 'Espace « ' . $nom . ' » supprimé.', type: 'info');
        } catch (\Throwable $e) {
            $this->dispatch('toast', message: 'Erreur : ' . $e->getMessage(), type: 'error');
        }
    }

    // =========================================================
    // Réinitialiser tous les filtres
    // =========================================================
    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterType = '';
        $this->filterStatut = '';
        $this->resetPage();
    }

    // =========================================================
    // Render
    // =========================================================
    public function render()
    {
        $columns = $this->getAvailableColumns();

        $espaces = Espace::query()
            ->when($this->search, fn($q) =>
                $q->where(fn($sub) =>
                    $sub->where('nom', 'like', "%{$this->search}%")
                        ->orWhere('description', 'like', "%{$this->search}%")
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

        // Taux d'occupation moyen des espaces actifs
        $tauxMoyen = Espace::active()->get()
            ->avg(fn($e) => $e->taux_occupation ?? 0) ?? 0;

        // Colonnes disponibles (pour affichage conditionnel)
        $hasCapaciteMinMax  = in_array('capacite_min', $columns);
        $hasPrixJourneeMois = in_array('prix_journee', $columns);
        $hasPhoto           = in_array('photo_principale', $columns);

        return view('livewire.admin-espaces', compact(
            'espaces', 'tauxMoyen',
            'hasCapaciteMinMax', 'hasPrixJourneeMois', 'hasPhoto'
        ));
    }
}