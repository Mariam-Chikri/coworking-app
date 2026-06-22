<div>
    {{-- ══════════════════════════════════════
         EN-TÊTE
    ═══════════════════════════════════════ --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h2 style="font-size:1.5rem;font-weight:700;margin:0">
                <i class="fas fa-building" style="color:var(--primary);margin-right:0.5rem"></i>
                Gestion des Espaces
            </h2>
            <p style="color:var(--gray-500);margin:0.25rem 0 0 0;font-size:0.9rem">
                Gérez tous les espaces de coworking disponibles
            </p>
        </div>
        <button wire:click="openCreate" class="cw-btn cw-btn-primary">
            <i class="fas fa-plus"></i> Ajouter un espace
        </button>
    </div>

    {{-- ══════════════════════════════════════
         KPI
    ═══════════════════════════════════════ --}}
    <div class="cw-admin-grid" style="grid-template-columns:repeat(auto-fit,minmax(180px,1fr));margin-bottom:1.5rem">
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-warehouse"></i></div>
            <div><div class="cw-kpi-value">{{ $espaces->total() }}</div><div class="cw-kpi-label">Total espaces</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-check-circle"></i></div>
            <div><div class="cw-kpi-value">{{ App\Models\Espace::where('actif',true)->count() }}</div><div class="cw-kpi-label">Actifs</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-pause-circle"></i></div>
            <div><div class="cw-kpi-value">{{ App\Models\Espace::where('actif',false)->count() }}</div><div class="cw-kpi-label">Désactivés</div></div>
        </div>
        <div class="cw-kpi-card" title="% des heures disponibles ce mois réellement réservées">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9)"><i class="fas fa-chart-line"></i></div>
            <div><div class="cw-kpi-value">{{ round($tauxMoyen, 1) }}%</div><div class="cw-kpi-label">Taux d'occupation <i class="fas fa-info-circle" style="font-size:.7rem;opacity:.5"></i></div></div>
        </div>
    </div>

    {{-- Explication taux d'occupation --}}
    <div style="background:rgba(102,126,234,.06);border:1px solid rgba(102,126,234,.2);border-radius:8px;padding:.65rem 1rem;margin-bottom:1.25rem;font-size:.8rem;color:var(--gray-600)">
        <i class="fas fa-info-circle" style="color:var(--primary)"></i>
        <strong>Taux d'occupation</strong> = % des heures ouvrables du mois durant lesquelles l'espace est réservé.
        Un espace ne peut être occupé que par <strong>un seul utilisateur à la fois</strong>.
    </div>

    {{-- ══════════════════════════════════════
         FILTRES
    ═══════════════════════════════════════ --}}
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center">
        <div style="position:relative;flex:1;min-width:200px">
            <i class="fas fa-search" style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--gray-400);pointer-events:none"></i>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="Rechercher par nom, description, adresse…"
                   class="cw-input" style="padding-left:2.5rem" autocomplete="off">
        </div>
        <select wire:model.live="filterType" class="cw-select" style="width:auto;min-width:160px">
            <option value="">Tous les types</option>
            <option value="bureau_individuel">Bureau individuel</option>
            <option value="bureau_prive">Bureau privé</option>
            <option value="open_space">Open space</option>
            <option value="salle_reunion">Salle de réunion</option>
            <option value="salle_conference">Salle de conférence</option>
        </select>
        <select wire:model.live="filterStatut" class="cw-select" style="width:auto;min-width:140px">
            <option value="">Tous les statuts</option>
            <option value="1">Actif</option>
            <option value="0">Désactivé</option>
        </select>
        <button wire:click="$set('search','');$set('filterType','');$set('filterStatut','')"
                class="cw-btn cw-btn-outline">
            <i class="fas fa-undo"></i> Réinitialiser
        </button>
    </div>

    {{-- ══════════════════════════════════════
         TABLEAU
    ═══════════════════════════════════════ --}}
    <div class="cw-table-wrap">
        <div wire:loading.delay.short style="text-align:center;padding:1.5rem">
            <i class="fas fa-spinner fa-spin" style="color:var(--primary);font-size:1.5rem"></i>
        </div>
        <table class="cw-table" wire:loading.remove>
            <thead>
                <tr>
                    <th>#</th><th>Espace</th><th>Type</th><th>Capacité</th>
                    <th>Prix</th><th>Taux occ.</th><th>Statut</th><th>Réservations</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($espaces as $espace)
                <tr>
                    <td style="font-size:.8rem;color:var(--gray-400)">{{ $espace->id }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.75rem">
                            <div style="width:44px;height:44px;border-radius:8px;overflow:hidden;flex-shrink:0">
                                <img src="{{ $espace->photo_url }}" alt="{{ $espace->nom }}"
                                     style="width:100%;height:100%;object-fit:cover"
                                     onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $espace->id }}/44/44'">
                            </div>
                            <div>
                                <strong>{{ $espace->nom }}</strong><br>
                                <small style="color:var(--gray-400);font-size:.75rem">{{ Str::limit($espace->description ?? '—', 50) }}</small>
                            </div>
                        </div>
                    </td>
                    <td><span class="cw-pill" style="background:var(--gradient-soft);color:var(--primary)">{{ $espace->type_label }}</span></td>
                    <td style="white-space:nowrap">{{ $espace->capacite_min ?? 1 }} – {{ $espace->capacite_max ?? 1 }} pers.</td>
                    <td style="font-weight:600;color:var(--primary)">
                        @if($espace->prix_heure > 0) {{ number_format($espace->prix_heure,0) }} DH/h
                        @elseif($espace->prix_journee > 0) {{ number_format($espace->prix_journee,0) }} DH/j
                        @elseif($espace->prix_mois > 0) {{ number_format($espace->prix_mois,0) }} DH/mois
                        @else — @endif
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.5rem">
                            <div style="flex:1;height:6px;background:var(--gray-100);border-radius:999px;min-width:44px">
                                <div style="height:100%;width:{{ min($espace->taux_occupation,100) }}%;background:var(--gradient);border-radius:999px"></div>
                            </div>
                            <span style="font-size:.78rem;font-weight:600;color:var(--primary)">{{ $espace->taux_occupation }}%</span>
                        </div>
                    </td>
                    <td><span class="cw-statut-badge {{ $espace->actif ? 'confirmee' : 'annulee' }}">{{ $espace->actif ? 'Actif' : 'Désactivé' }}</span></td>
                    <td style="text-align:center">{{ $espace->reservations_count ?? 0 }}</td>
                    <td>
                        <div style="display:flex;gap:.4rem">
                            <button wire:click="openEdit({{ $espace->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="Modifier"><i class="fas fa-edit"></i></button>
                            <button wire:click="toggleActif({{ $espace->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="{{ $espace->actif ? 'Désactiver' : 'Activer' }}"><i class="fas fa-{{ $espace->actif ? 'pause' : 'play' }}"></i></button>
                            <button wire:click="confirmDelete({{ $espace->id }})" class="cw-btn cw-btn-danger cw-btn-xs" title="Supprimer"><i class="fas fa-trash"></i></button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:3rem;color:var(--gray-400)">
                        <i class="fas fa-search" style="font-size:2rem;display:block;margin-bottom:.75rem;color:var(--gray-200)"></i>
                        Aucun espace trouvé.<br><small>Modifiez les filtres ou ajoutez un espace.</small>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:1.5rem">{{ $espaces->links() }}</div>


    {{-- ══════════════════════════════════════════════════════════════
         MODAL CRÉER / MODIFIER
         ► Pas de <form> avec wire:submit pour éviter les rechargements.
           Le bouton "Sauvegarder" utilise wire:click="save" (type="button").
    ══════════════════════════════════════════════════════════════ --}}
    @if($showModal)
    <div class="cw-modal-overlay" wire:click.self="$set('showModal',false)">
        <div class="cw-modal" style="max-width:640px;max-height:92vh;overflow-y:auto">

            {{-- En-tête --}}
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
                <h3 style="margin:0;font-size:1.15rem;font-weight:700">
                    <i class="fas fa-{{ $espaceId ? 'edit' : 'plus' }}" style="color:var(--primary)"></i>
                    {{ $espaceId ? 'Modifier l\'espace' : 'Nouvel espace' }}
                </h3>
                <button wire:click="$set('showModal',false)"
                        type="button"
                        style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--gray-400)">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- ── Contenu du formulaire (pas de <form>, tout par wire:model + wire:click) ── --}}
            <div style="display:grid;gap:1.1rem">

                {{-- Nom --}}
                <div class="cw-form-group">
                    <label class="cw-label">Nom de l'espace *</label>
                    <input wire:model="form.nom" type="text" class="cw-input"
                           placeholder="Ex : Bureau Premium A" autocomplete="off">
                    @error('form.nom')<span class="cw-form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Type --}}
                <div class="cw-form-group">
                    <label class="cw-label">Type d'espace *</label>
                    <select wire:model="form.type" class="cw-select">
                        <option value="bureau_individuel">Bureau individuel</option>
                        <option value="bureau_prive">Bureau privé</option>
                        <option value="open_space">Open space</option>
                        <option value="salle_reunion">Salle de réunion</option>
                        <option value="salle_conference">Salle de conférence</option>
                    </select>
                    @error('form.type')<span class="cw-form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Description --}}
                <div class="cw-form-group">
                    <label class="cw-label">Description</label>
                    <textarea wire:model="form.description" class="cw-textarea" rows="3"
                              placeholder="Décrivez cet espace…"></textarea>
                    @error('form.description')<span class="cw-form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Capacité --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    <div class="cw-form-group">
                        <label class="cw-label">Capacité min *</label>
                        <input wire:model="form.capacite_min" type="number" class="cw-input" min="1">
                        @error('form.capacite_min')<span class="cw-form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="cw-form-group">
                        <label class="cw-label">Capacité max *</label>
                        <input wire:model="form.capacite_max" type="number" class="cw-input" min="1">
                        @error('form.capacite_max')<span class="cw-form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Prix --}}
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:1rem">
                    <div class="cw-form-group">
                        <label class="cw-label">Prix/heure (DH) *</label>
                        <input wire:model="form.prix_heure" type="number" class="cw-input" min="0" step="0.5">
                        @error('form.prix_heure')<span class="cw-form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="cw-form-group">
                        <label class="cw-label">Prix/jour (DH)</label>
                        <input wire:model="form.prix_journee" type="number" class="cw-input" min="0" step="0.5" placeholder="Optionnel">
                        @error('form.prix_journee')<span class="cw-form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="cw-form-group">
                        <label class="cw-label">Prix/mois (DH)</label>
                        <input wire:model="form.prix_mois" type="number" class="cw-input" min="0" step="0.5" placeholder="Optionnel">
                        @error('form.prix_mois')<span class="cw-form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Adresse --}}
                <div class="cw-form-group">
                    <label class="cw-label">Adresse</label>
                    <input wire:model="form.adresse" type="text" class="cw-input"
                           placeholder="Ex : 12 Rue des Entrepreneurs, Casablanca">
                    @error('form.adresse')<span class="cw-form-error">{{ $message }}</span>@enderror
                </div>

                {{-- ══════════════════════════════
                     PHOTO PRINCIPALE
                     1. Aperçu de l'image existante (si mode édition)
                     2. Sélecteur de nouvelle image (wire:model="nouvellePhoto")
                     3. Aperçu temporaire de la nouvelle image
                ═══════════════════════════════ --}}
                <div class="cw-form-group">
                    <label class="cw-label">
                        <i class="fas fa-image" style="color:var(--primary)"></i>
                        Photo de l'espace
                        <span style="font-size:.75rem;font-weight:400;color:var(--gray-400)">(JPG/PNG/WebP, max 2 Mo)</span>
                    </label>

                    {{-- A) Image existante --}}
                    @if($photoExistante && !$supprimerPhotoExistante)
                        <div style="margin-bottom:.75rem">
                            <p style="font-size:.78rem;color:var(--gray-500);margin:0 0 .35rem 0">
                                <i class="fas fa-check-circle" style="color:var(--success)"></i> Photo actuelle :
                            </p>
                            <div style="position:relative;display:inline-block">
                                <img src="{{ asset('storage/' . $photoExistante) }}"
                                     alt="Photo actuelle"
                                     style="max-height:140px;max-width:100%;border-radius:8px;border:2px solid var(--gray-200);object-fit:cover">
                                <button type="button"
                                        wire:click="supprimerPhoto"
                                        style="position:absolute;top:-8px;right:-8px;background:#ef4444;color:white;border:none;border-radius:50%;width:24px;height:24px;cursor:pointer;font-size:.75rem;display:flex;align-items:center;justify-content:center"
                                        title="Supprimer cette photo">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                            <p style="font-size:.73rem;color:var(--gray-400);margin:.35rem 0 0 0">
                                Cliquez <i class="fas fa-times" style="color:#ef4444"></i> pour supprimer, ou sélectionnez une nouvelle image pour remplacer.
                            </p>
                        </div>
                    @elseif($supprimerPhotoExistante)
                        <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:6px;padding:.5rem .85rem;margin-bottom:.65rem;font-size:.8rem;color:#b91c1c">
                            <i class="fas fa-exclamation-triangle"></i>
                            Photo supprimée — elle sera retirée définitivement à la sauvegarde.
                        </div>
                    @endif

                    {{-- B) Sélecteur de fichier --}}
                    <label for="photo-input-{{ $espaceId ?? 'new' }}"
                           style="display:block;border:2px dashed var(--gray-300);border-radius:10px;padding:1.25rem;text-align:center;cursor:pointer;transition:.2s;background:var(--gray-50)"
                           onmouseover="this.style.borderColor='var(--primary)';this.style.background='rgba(102,126,234,.04)'"
                           onmouseout="this.style.borderColor='var(--gray-300)';this.style.background='var(--gray-50)'">
                        <i class="fas fa-cloud-upload-alt" style="font-size:2rem;color:var(--gray-300);display:block;margin-bottom:.4rem"></i>
                        <span style="font-size:.88rem;font-weight:500;color:var(--gray-600)">
                            {{ ($photoExistante && !$supprimerPhotoExistante) ? 'Remplacer la photo' : 'Choisir une photo' }}
                        </span>
                    </label>
                    <input id="photo-input-{{ $espaceId ?? 'new' }}"
                           wire:model="nouvellePhoto"
                           type="file"
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           style="display:none">

                    {{-- Loading pendant l'upload Livewire --}}
                    <div wire:loading wire:target="nouvellePhoto"
                         style="text-align:center;padding:.5rem;color:var(--primary);font-size:.85rem">
                        <i class="fas fa-spinner fa-spin"></i> Chargement en cours…
                    </div>

                    {{-- C) Aperçu de la nouvelle photo --}}
                    @if($nouvellePhoto)
                        <div style="margin-top:.65rem">
                            <p style="font-size:.78rem;color:var(--gray-500);margin:0 0 .35rem 0">
                                <i class="fas fa-eye" style="color:var(--primary)"></i> Aperçu de la nouvelle photo :
                            </p>
                            <div style="position:relative;display:inline-block">
                                <img src="{{ $nouvellePhoto->temporaryUrl() }}"
                                     alt="Aperçu"
                                     style="max-height:140px;max-width:100%;border-radius:8px;border:2px solid var(--success);object-fit:cover">
                                <span style="position:absolute;bottom:5px;right:5px;background:var(--success);color:white;padding:.15rem .5rem;border-radius:3px;font-size:.68rem;font-weight:700">Nouvelle</span>
                            </div>
                        </div>
                    @endif

                    @error('nouvellePhoto')<span class="cw-form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Actif --}}
                <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem;background:var(--gray-50);border-radius:8px">
                    <input type="checkbox" wire:model="form.actif" id="espace-actif-cb"
                           style="width:18px;height:18px;accent-color:var(--primary)">
                    <label for="espace-actif-cb" style="margin:0;font-weight:500;cursor:pointer">
                        Espace actif et réservable
                    </label>
                </div>

            </div>{{-- fin grille --}}

            {{-- ── Boutons d'action ── --}}
            <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.75rem;padding-top:1.25rem;border-top:1px solid var(--gray-100)">
                <button type="button"
                        wire:click="$set('showModal',false)"
                        class="cw-btn cw-btn-outline">
                    Annuler
                </button>
                {{-- ► wire:click="save" sur type="button" — pas de form submit --}}
                <button type="button"
                        wire:click="save"
                        class="cw-btn cw-btn-primary">
                    <span wire:loading.remove wire:target="save">
                        <i class="fas fa-save"></i> {{ $espaceId ? 'Mettre à jour' : 'Créer l\'espace' }}
                    </span>
                    <span wire:loading wire:target="save">
                        <i class="fas fa-spinner fa-spin"></i> Enregistrement…
                    </span>
                </button>
            </div>

        </div>
    </div>
    @endif


    {{-- ══════════════════════════════════════
         MODAL SUPPRESSION
    ═══════════════════════════════════════ --}}
    @if($showDeleteModal)
    <div class="cw-modal-overlay" wire:click.self="$set('showDeleteModal',false)">
        <div class="cw-modal" style="max-width:420px">
            <h3 style="color:var(--danger);margin-bottom:.75rem">
                <i class="fas fa-exclamation-triangle"></i> Supprimer l'espace ?
            </h3>
            <p style="color:var(--gray-700);margin:0 0 .5rem 0">Cette action est <strong>irréversible</strong>.</p>
            <p style="font-size:.84rem;color:var(--danger);margin:0">
                L'espace, sa photo et toutes les réservations associées seront supprimés définitivement.
            </p>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--gray-100)">
                <button type="button" wire:click="$set('showDeleteModal',false)" class="cw-btn cw-btn-outline">Annuler</button>
                <button type="button" wire:click="delete" class="cw-btn cw-btn-danger">
                    <span wire:loading.remove wire:target="delete"><i class="fas fa-trash"></i> Supprimer</span>
                    <span wire:loading wire:target="delete"><i class="fas fa-spinner fa-spin"></i> Suppression…</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

