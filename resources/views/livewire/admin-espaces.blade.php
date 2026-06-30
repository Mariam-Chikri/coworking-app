<div>

    {{-- ══════════════════════════════════════
         EN-TÊTE
    ═══════════════════════════════════════ --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem;flex-wrap:wrap;gap:1rem">
        <div>
            <h2 style="font-size:1.5rem;font-weight:700;margin:0">
                <i class="fas fa-building" style="color:var(--primary)"></i>
                {{ __('messages.gestion_espaces') }}
            </h2>
            <p style="color:var(--gray-500);margin:.25rem 0 0;font-size:.9rem">
                {{ __('messages.admin_espaces_sous_titre') }}
            </p>
        </div>
        <button type="button" wire:click="openCreate" class="cw-btn cw-btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.admin_espaces_ajouter') }}
        </button>
    </div>

    {{-- ══════════════════════════════════════
         ALERTE MIGRATION MANQUANTE
    ═══════════════════════════════════════ --}}
    @if(!$hasCapaciteMinMax)
    <div style="background:#fef3c7;border:1px solid #f59e0b;border-radius:8px;padding:.85rem 1.1rem;margin-bottom:1.25rem;font-size:.85rem;color:#92400e">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>{{ __('messages.admin_espaces_migration_manquante') }}</strong> — {{ __('messages.admin_espaces_migration_manquante_detail') }}<br>
        {{ __('messages.admin_espaces_migration_executer') }}
    </div>
    @endif

    {{-- ══════════════════════════════════════
         KPI
    ═══════════════════════════════════════ --}}
    <div class="cw-admin-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));margin-bottom:1.5rem">
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-warehouse"></i></div>
            <div><div class="cw-kpi-value">{{ $espaces->total() }}</div><div class="cw-kpi-label">{{ __('messages.admin_espaces_total') }}</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-check-circle"></i></div>
            <div><div class="cw-kpi-value">{{ App\Models\Espace::where('actif',true)->count() }}</div><div class="cw-kpi-label">{{ __('messages.actif') }}</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-pause-circle"></i></div>
            <div><div class="cw-kpi-value">{{ App\Models\Espace::where('actif',false)->count() }}</div><div class="cw-kpi-label">{{ __('messages.inactif') }}</div></div>
        </div>
        <div class="cw-kpi-card" title="% des heures disponibles ce mois réellement réservées">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9)"><i class="fas fa-chart-line"></i></div>
            <div><div class="cw-kpi-value">{{ round($tauxMoyen,1) }}%</div><div class="cw-kpi-label">{{ __('messages.taux_occupation') }}</div></div>
        </div>
    </div>

    {{-- ══════════════════════════════════════
         FILTRES
    ═══════════════════════════════════════ --}}
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center">
        <div style="position:relative;flex:1;min-width:200px">
            <i class="fas fa-search" style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--gray-400);pointer-events:none"></i>
            <input wire:model.live.debounce.300ms="search" type="text"
                   placeholder="{{ __('messages.admin_espaces_rechercher') }}"
                   class="cw-input" style="padding-left:2.5rem" autocomplete="off">
        </div>
        <select wire:model.live="filterType" class="cw-select" style="width:auto;min-width:160px">
            <option value="">{{ __('messages.tous_types') }}</option>
            <option value="bureau_individuel">{{ __('messages.bureau_individuel') }}</option>
            <option value="bureau_prive">{{ __('messages.bureau_prive') }}</option>
            <option value="open_space_creatif">{{ __('messages.open_space_creatif') }}</option>
            <option value="salle_reunion">{{ __('messages.salle_reunion') }}</option>
            <option value="salle_conference">{{ __('messages.salle_conference') }}</option>
            <option value="non_reservable">{{ __('messages.non_reservable') }}</option>
        </select>
        <select wire:model.live="filterStatut" class="cw-select" style="width:auto;min-width:140px">
            <option value="">{{ __('messages.admin_espaces_tous_statuts') }}</option>
            <option value="1">{{ __('messages.actif') }}</option>
            <option value="0">{{ __('messages.inactif') }}</option>
        </select>
        <button type="button" wire:click="$set('search','');$set('filterType','');$set('filterStatut','')"
                class="cw-btn cw-btn-outline">
            <i class="fas fa-undo"></i> {{ __('messages.reinitialiser') }}
        </button>
    </div>

    {{-- ══════════════════════════════════════
         TABLEAU AVEC SCROLL
    ═══════════════════════════════════════ --}}
    <div class="cw-table-wrap-scroll" style="max-height:500px;overflow-y:auto;border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--gray-100);position:relative">
        <div wire:loading.delay style="text-align:center;padding:1.5rem;position:sticky;top:0;background:white;z-index:10">
            <i class="fas fa-spinner fa-spin" style="color:var(--primary);font-size:1.5rem"></i>
        </div>
        <table class="cw-table" style="width:100%;border-collapse:collapse">
            <thead style="position:sticky;top:0;z-index:5;background:white">
                <tr>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_espaces_id') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_espaces_espace') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_espaces_type') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_espaces_capacite') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_espaces_prix') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_espaces_taux') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_espaces_statut') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:center">{{ __('messages.admin_espaces_reservations') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:center;min-width:150px">{{ __('messages.admin_espaces_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($espaces as $espace)
                <tr style="border-bottom:1px solid var(--gray-100)">
                    <td style="padding:0.75rem 1rem;font-size:.8rem;color:var(--gray-400)">{{ $espace->id }}</td>
                    <td style="padding:0.75rem 1rem">
                        <div style="display:flex;align-items:center;gap:.75rem">
                            <div style="width:44px;height:44px;border-radius:8px;overflow:hidden;flex-shrink:0;background:var(--gradient)">
                                <img src="{{ $espace->photo_url }}"
                                     alt="{{ $espace->nom }}"
                                     style="width:100%;height:100%;object-fit:cover"
                                     onerror="this.onerror=null;this.style.display='none'">
                            </div>
                            <div>
                                <strong>{{ $espace->nom }}</strong><br>
                                <small style="color:var(--gray-400);font-size:.75rem">
                                    {{ Str::limit($espace->description ?? '—', 45) }}
                                </small>
                            </div>
                        </div>
                    </td>
                    <td style="padding:0.75rem 1rem"><span class="cw-pill" style="background:var(--gradient-soft);color:var(--primary)">{{ $espace->type_label }}</span></td>
                    <td style="padding:0.75rem 1rem;white-space:nowrap">
                        @if($hasCapaciteMinMax)
                            {{ $espace->capacite_min ?? 1 }} – {{ $espace->capacite_max ?? 1 }} {{ __('messages.admin_espaces_personnes') }}
                            @if($espace->type === 'open_space_creatif' && isset($espace->nombre_bureaux))
                                <br><small style="color:var(--gray-400);font-size:.7rem">{{ $espace->nombre_bureaux }} {{ __('messages.admin_espaces_bureaux') }}</small>
                            @endif
                        @else
                            {{ $espace->capacite ?? '?' }} {{ __('messages.admin_espaces_personnes') }}
                        @endif
                    </td>
                    <td style="padding:0.75rem 1rem;font-weight:600;color:var(--primary)">
                        @if($espace->type === 'non_reservable')
                            <span style="color:var(--gray-400)">_</span>
                        @else
                            {{ number_format($espace->prix_heure, 0) }} DH/h
                            @if($hasPrixJourneeMois && $espace->prix_journee)
                                <br>
                                <small style="font-size:.72rem;color:var(--gray-400)">
                                    {{ number_format($espace->prix_journee,0) }} DH/j
                                </small>
                            @endif
                        @endif
                    </td>
                    <td style="padding:0.75rem 1rem">
                        <div style="display:flex;align-items:center;gap:.4rem">
                            <div style="flex:1;height:5px;background:var(--gray-100);border-radius:999px;min-width:40px">
                                <div style="height:100%;width:{{ min($espace->taux_occupation,100) }}%;background:var(--gradient);border-radius:999px"></div>
                            </div>
                            <span style="font-size:.78rem;font-weight:600;color:var(--primary)">{{ $espace->taux_occupation }}%</span>
                        </div>
                    </td>
                    <td style="padding:0.75rem 1rem">
                        <span class="cw-statut-badge {{ $espace->actif ? 'confirmee' : 'annulee' }}">
                            {{ $espace->actif ? __('messages.actif') : __('messages.inactif') }}
                        </span>
                    </td>
                    <td style="padding:0.75rem 1rem;text-align:center">{{ $espace->reservations_count ?? 0 }}</td>
                    <td style="padding:0.75rem 1rem;text-align:center">
                        <div style="display:flex;gap:.35rem;justify-content:center">
                            <button type="button" wire:click="openEdit({{ $espace->id }})"
                                    class="cw-btn cw-btn-outline cw-btn-xs" title="{{ __('messages.admin_espaces_modifier') }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button type="button" wire:click="toggleActif({{ $espace->id }})"
                                    class="cw-btn cw-btn-outline cw-btn-xs"
                                    title="{{ $espace->actif ? __('messages.admin_espaces_desactiver') : __('messages.admin_espaces_activer') }}">
                                <i class="fas fa-{{ $espace->actif ? 'pause' : 'play' }}"></i>
                            </button>
                            <button type="button" wire:click="confirmDelete({{ $espace->id }})"
                                    class="cw-btn cw-btn-danger cw-btn-xs" title="{{ __('messages.admin_espaces_supprimer') }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:3rem;color:var(--gray-400)">
                        <i class="fas fa-search" style="font-size:2rem;display:block;margin-bottom:.75rem;color:var(--gray-200)"></i>
                        {{ __('messages.admin_espaces_aucun') }}
                        <br><small>{{ __('messages.admin_espaces_aucun_detail') }}</small>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Compteur --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem;padding:0.5rem 0;font-size:.85rem;color:var(--gray-500)">
        <span>
            <i class="fas fa-database" style="color:var(--primary);margin-right:0.5rem"></i>
            <strong>{{ $espaces->total() }}</strong> 
            {{ app()->getLocale() === 'en' ? 'spaces displayed' : 'espaces affichés' }}
        </span>
        <span style="display:flex;align-items:center;gap:0.5rem">
            <i class="fas fa-arrow-down" style="color:var(--primary)"></i>
            {{ app()->getLocale() === 'en' ? 'Scroll to view more' : 'Scrollez pour voir plus' }}
        </span>
    </div>

    {{-- ══════════════════════════════════════
         PAGINATION PERSONNALISÉE
    ═══════════════════════════════════════ --}}
    @if($espaces->hasPages())
    <div class="cw-pagination-wrapper">
        <div class="cw-pagination">
            {{-- Bouton Précédent --}}
            @if($espaces->onFirstPage())
                <span class="cw-page-btn cw-page-btn-prev disabled" aria-disabled="true">
                    <i class="fas fa-chevron-left"></i>
                    <span>{{ app()->getLocale() === 'en' ? 'Previous' : __('messages.admin_espaces_precedent') }}</span>
                </span>
            @else
                <button wire:click="previousPage" wire:loading.attr="disabled" class="cw-page-btn cw-page-btn-prev">
                    <i class="fas fa-chevron-left"></i>
                    <span>{{ app()->getLocale() === 'en' ? 'Previous' : __('messages.admin_espaces_precedent') }}</span>
                </button>
            @endif

            {{-- Numéros de pages --}}
            <div class="cw-page-numbers">
                @php
                    $currentPage = $espaces->currentPage();
                    $lastPage = $espaces->lastPage();
                    $range = 2;
                @endphp

                @if($currentPage > $range + 1)
                    <button wire:click="gotoPage(1)" class="cw-page-btn cw-page-btn-number">1</button>
                    @if($currentPage > $range + 2)
                        <span class="cw-page-ellipsis">…</span>
                    @endif
                @endif

                @for($page = max(1, $currentPage - $range); $page <= min($lastPage, $currentPage + $range); $page++)
                    @if($page == $currentPage)
                        <span class="cw-page-btn cw-page-btn-number active" aria-current="page">
                            {{ $page }}
                        </span>
                    @else
                        <button wire:click="gotoPage({{ $page }})" class="cw-page-btn cw-page-btn-number">
                            {{ $page }}
                        </button>
                    @endif
                @endfor

                @if($currentPage < $lastPage - $range)
                    @if($currentPage < $lastPage - $range - 1)
                        <span class="cw-page-ellipsis">…</span>
                    @endif
                    <button wire:click="gotoPage({{ $lastPage }})" class="cw-page-btn cw-page-btn-number">
                        {{ $lastPage }}
                    </button>
                @endif
            </div>

            {{-- Bouton Suivant --}}
            @if($espaces->hasMorePages())
                <button wire:click="nextPage" wire:loading.attr="disabled" class="cw-page-btn cw-page-btn-next">
                    <span>{{ app()->getLocale() === 'en' ? 'Next' : __('messages.admin_espaces_suivant') }}</span>
                    <i class="fas fa-chevron-right"></i>
                </button>
            @else
                <span class="cw-page-btn cw-page-btn-next disabled" aria-disabled="true">
                    <span>{{ app()->getLocale() === 'en' ? 'Next' : __('messages.admin_espaces_suivant') }}</span>
                    <i class="fas fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════════
         MODAL CRÉER / MODIFIER
    ══════════════════════════════════════════════════════════════════════ --}}
    @if($showModal)
    <div class="cw-modal-overlay" wire:click.self="$set('showModal',false)">
        <div class="cw-modal" style="max-width:640px;max-height:92vh;overflow-y:auto">

            {{-- En-tête --}}
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
                <h3 style="margin:0;font-size:1.1rem;font-weight:700">
                    <i class="fas fa-{{ $espaceId ? 'edit' : 'plus' }}" style="color:var(--primary)"></i>
                    {{ $espaceId ? __('messages.admin_espaces_modal_titre_modifier') : __('messages.admin_espaces_modal_titre_creer') }}
                </h3>
                <button type="button" wire:click="$set('showModal',false)"
                        style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--gray-400)">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Formulaire — propriétés individuelles --}}
            <div style="display:grid;gap:1.1rem">

                {{-- Nom --}}
                <div class="cw-form-group">
                    <label class="cw-label">{{ __('messages.admin_espaces_nom') }} *</label>
                    <input wire:model="fNom"
                           type="text"
                           class="cw-input @error('fNom') cw-input-error @enderror"
                           placeholder="{{ __('messages.admin_espaces_nom_placeholder') }}"
                           autocomplete="off">
                    @error('fNom')<span class="cw-form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Type --}}
                <div class="cw-form-group">
                    <label class="cw-label">{{ __('messages.admin_espaces_type_label') }} *</label>
                    <select wire:model.live="fType" class="cw-select">
                        <option value="">{{ __('messages.tous_types') }}</option>
                        <option value="bureau_individuel">{{ __('messages.bureau_individuel') }}</option>
                        <option value="bureau_prive">{{ __('messages.bureau_prive') }}</option>
                        <option value="open_space_creatif">{{ __('messages.open_space_creatif') }}</option>
                        <option value="salle_reunion">{{ __('messages.salle_reunion') }}</option>
                        <option value="salle_conference">{{ __('messages.salle_conference') }}</option>
                        <option value="non_reservable">{{ __('messages.non_reservable') }}</option>
                    </select>
                    @error('fType')<span class="cw-form-error">{{ $message }}</span>@enderror
                </div>

                {{-- Nombre de bureaux (Open Space Créatif uniquement) --}}
                <div wire:key="nombre-bureaux-container" 
                     x-data="{ show: $wire.fType === 'open_space_creatif' }"
                     x-show="show"
                     x-transition.duration.300ms
                     style="background:#f0f9ff;border-left:3px solid #0ea5e9;padding:.75rem 1rem;border-radius:.5rem"
                     x-init="$watch('$wire.fType', value => show = value === 'open_space_creatif')">
                    <div class="cw-form-group">
                        <label class="cw-label" style="color:#0369a1">
                            <i class="fas fa-chair"></i> {{ __('messages.admin_espaces_nombre_bureaux') }} *
                            <span style="font-size:.75rem;color:#64748b;font-weight:400"> — {{ __('messages.admin_espaces_nombre_bureaux_aide') }}</span>
                        </label>
                        <input wire:model="fNombreBureaux"
                               type="number"
                               class="cw-input @error('fNombreBureaux') cw-input-error @enderror"
                               min="1" max="500"
                               placeholder="{{ __('messages.admin_espaces_nombre_bureaux_placeholder') }}"
                               x-bind:required="show">
                        @error('fNombreBureaux')<span class="cw-form-error">{{ $message }}</span>@enderror
                    </div>
                </div>

                {{-- Description --}}
                <div class="cw-form-group">
                    <label class="cw-label">{{ __('messages.admin_espaces_description') }}</label>
                    <textarea wire:model="fDescription"
                              class="cw-textarea" rows="3"
                              placeholder="{{ __('messages.admin_espaces_description_placeholder') }}"></textarea>
                </div>

                {{-- Capacité --}}
                @if($hasCapaciteMinMax)
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
                    <div class="cw-form-group">
                        <label class="cw-label">{{ __('messages.admin_espaces_capacite_min') }} *</label>
                        <input wire:model="fCapaciteMin"
                               type="number" class="cw-input @error('fCapaciteMin') cw-input-error @enderror" min="1">
                        @error('fCapaciteMin')<span class="cw-form-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="cw-form-group">
                        <label class="cw-label">{{ __('messages.admin_espaces_capacite_max') }} *</label>
                        <input wire:model="fCapaciteMax"
                               type="number" class="cw-input @error('fCapaciteMax') cw-input-error @enderror" min="1">
                        @error('fCapaciteMax')<span class="cw-form-error">{{ $message }}</span>@enderror
                    </div>
                </div>
                @endif

                {{-- Prix (caché si non_reservable) --}}
                <div wire:key="prix-container"
                     x-data="{ show: $wire.fType !== 'non_reservable' }"
                     x-show="show"
                     x-transition.duration.300ms
                     x-init="$watch('$wire.fType', value => show = value !== 'non_reservable')">
                    <div style="display:grid;grid-template-columns:1fr {{ $hasPrixJourneeMois ? '1fr 1fr' : '' }};gap:1rem">
                        <div class="cw-form-group">
                            <label class="cw-label">{{ __('messages.admin_espaces_prix_heure') }} *</label>
                            <input wire:model="fPrixHeure"
                                   type="number" class="cw-input @error('fPrixHeure') cw-input-error @enderror" min="0" step="0.5">
                            @error('fPrixHeure')<span class="cw-form-error">{{ $message }}</span>@enderror
                        </div>
                        @if($hasPrixJourneeMois)
                        <div class="cw-form-group">
                            <label class="cw-label">{{ __('messages.admin_espaces_prix_jour') }}</label>
                            <input wire:model="fPrixJournee"
                                   type="number" class="cw-input" min="0" step="0.5" placeholder="Optionnel">
                        </div>
                        <div class="cw-form-group">
                            <label class="cw-label">{{ __('messages.admin_espaces_prix_mois') }}</label>
                            <input wire:model="fPrixMois"
                                   type="number" class="cw-input" min="0" step="0.5" placeholder="Optionnel">
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Photo principale --}}
                @if($hasPhoto)
                <div class="cw-form-group">
                    <label class="cw-label">
                        <i class="fas fa-image" style="color:var(--primary)"></i>
                        {{ __('messages.admin_espaces_photo') }}
                        <span style="font-size:.75rem;font-weight:400;color:var(--gray-400)">{{ __('messages.admin_espaces_photo_aide') }}</span>
                    </label>

                    {{-- Image existante --}}
                    @if($photoExistante && !$supprimerPhotoFlag)
                    <div style="margin-bottom:.75rem">
                        <div style="position:relative;display:inline-block">
                            <img src="{{ asset('storage/' . $photoExistante) }}"
                                 alt="Photo actuelle"
                                 style="max-height:130px;border-radius:8px;border:2px solid var(--gray-200);object-fit:cover">
                            <button type="button"
                                    wire:click="supprimerPhoto"
                                    style="position:absolute;top:-8px;right:-8px;background:#ef4444;color:white;border:none;border-radius:50%;width:24px;height:24px;cursor:pointer;font-size:.72rem;display:flex;align-items:center;justify-content:center"
                                    title="{{ __('messages.admin_espaces_photo_supprimer') }}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <p style="font-size:.73rem;color:var(--gray-400);margin:.3rem 0 0 0">
                            {{ __('messages.admin_espaces_photo_remplacer') }}
                        </p>
                    </div>
                    @elseif($supprimerPhotoFlag)
                    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:6px;padding:.5rem .85rem;margin-bottom:.65rem;font-size:.8rem;color:#b91c1c">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ __('messages.admin_espaces_photo_supprimee') }}
                    </div>
                    @endif

                    {{-- Sélecteur --}}
                    <label for="photo-upload-{{ $espaceId ?? 'new' }}"
                           style="display:block;border:2px dashed var(--gray-300);border-radius:10px;padding:1.25rem;text-align:center;cursor:pointer;background:var(--gray-50)"
                           onmouseover="this.style.borderColor='var(--primary)';this.style.background='rgba(102,126,234,.04)'"
                           onmouseout="this.style.borderColor='var(--gray-300)';this.style.background='var(--gray-50)'">
                        <i class="fas fa-cloud-upload-alt" style="font-size:1.8rem;color:var(--gray-300);display:block;margin-bottom:.3rem"></i>
                        <span style="font-size:.88rem;font-weight:500;color:var(--gray-600)">
                            {{ $photoExistante && !$supprimerPhotoFlag ? __('messages.admin_espaces_photo_remplacer_btn') : __('messages.admin_espaces_photo_choisir') }}
                        </span>
                    </label>
                    <input id="photo-upload-{{ $espaceId ?? 'new' }}"
                           wire:model="nouvellePhoto"
                           type="file"
                           accept="image/jpeg,image/png,image/jpg,image/webp"
                           style="display:none">

                    <div wire:loading wire:target="nouvellePhoto"
                         style="text-align:center;padding:.5rem;color:var(--primary);font-size:.85rem">
                        <i class="fas fa-spinner fa-spin"></i> {{ __('messages.admin_espaces_modal_enregistrement') }}
                    </div>

                    @if($nouvellePhoto)
                    <div style="margin-top:.65rem">
                        <p style="font-size:.78rem;color:var(--gray-500);margin:0 0 .35rem 0">
                            <i class="fas fa-eye" style="color:var(--primary)"></i> {{ __('messages.admin_espaces_photo_apercu') }} :
                        </p>
                        <img src="{{ $nouvellePhoto->temporaryUrl() }}"
                             style="max-height:130px;border-radius:8px;border:2px solid var(--success)">
                        <p style="font-size:.7rem;color:var(--gray-400);margin:.2rem 0 0">
                            {{ __('messages.admin_espaces_photo_taille') }} : {{ round($nouvellePhoto->getSize() / 1024 / 1024, 2) }} MB
                        </p>
                    </div>
                    @endif

                    @error('nouvellePhoto')<span class="cw-form-error">{{ $message }}</span>@enderror
                </div>
                @endif

                {{-- Actif --}}
                <div style="display:flex;align-items:center;gap:.75rem;padding:.5rem;background:var(--gray-50);border-radius:8px">
                    <input type="checkbox" wire:model="fActif" id="espace-actif-chk"
                           style="width:18px;height:18px;accent-color:var(--primary)">
                    <label for="espace-actif-chk" style="margin:0;font-weight:500;cursor:pointer">
                        {{ __('messages.admin_espaces_actif_checkbox') }}
                    </label>
                </div>

            </div>{{-- fin grille --}}

            {{-- Boutons --}}
            <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.75rem;padding-top:1.25rem;border-top:1px solid var(--gray-100)">
                <button type="button"
                        wire:click="$set('showModal',false)"
                        class="cw-btn cw-btn-outline">
                    {{ __('messages.annuler') }}
                </button>
                <button type="button"
                        wire:click="save"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        class="cw-btn cw-btn-primary">
                    <span wire:loading.remove wire:target="save">
                        <i class="fas fa-save"></i>
                        {{ $espaceId ? __('messages.admin_espaces_modal_modifier') : __('messages.admin_espaces_modal_creer') }}
                    </span>
                    <span wire:loading wire:target="save">
                        <i class="fas fa-spinner fa-spin"></i> {{ __('messages.admin_espaces_modal_enregistrement') }}
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
                <i class="fas fa-exclamation-triangle"></i> {{ __('messages.admin_espaces_supprimer_titre') }}
            </h3>
            <p style="color:var(--gray-700);margin:0 0 .5rem">
                {{ __('messages.admin_espaces_supprimer_irreversible') }}
            </p>
            <p style="font-size:.84rem;color:var(--danger)">
                {{ __('messages.admin_espaces_supprimer_detail') }}
            </p>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--gray-100)">
                <button type="button" wire:click="$set('showDeleteModal',false)" class="cw-btn cw-btn-outline">{{ __('messages.annuler') }}</button>
                <button type="button" wire:click="delete" class="cw-btn cw-btn-danger">
                    <span wire:loading.remove wire:target="delete"><i class="fas fa-trash"></i> {{ __('messages.admin_espaces_supprimer_confirmer') }}</span>
                    <span wire:loading wire:target="delete"><i class="fas fa-spinner fa-spin"></i></span>
                </button>
            </div>
        </div>
    </div>
    @endif

</div>