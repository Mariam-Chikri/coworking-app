<div>
    {{-- Filtres --}}
    <div class="cw-filters">
        <div class="cw-filters-grid">
            <div class="cw-field">
                <label>{{ __('messages.rechercher') }}</label>
                <input wire:model.live.debounce.400ms="recherche" type="text" class="cw-input"
                       placeholder="{{ __('messages.rechercher') }}" autocomplete="off">
            </div>
            <div class="cw-field">
                <label>{{ __('messages.type_espace') }}</label>
                <select wire:model.live="type" class="cw-select">
                    <option value="">{{ __('messages.tous_types') }}</option>
                    <option value="bureau_individuel">{{ __('messages.bureau_individuel') }}</option>
                    <option value="bureau_prive">{{ __('messages.bureau_prive') }}</option>
                    <option value="open_space">{{ __('messages.open_space') }}</option>
                    <option value="salle_reunion">{{ __('messages.salle_reunion') }}</option>
                    <option value="salle_conference">{{ __('messages.salle_conference') }}</option>
                </select>
            </div>
            <div class="cw-field">
                <label>{{ __('messages.capacite_min') }} ({{ $capaciteMin }})</label>
                <input wire:model.live="capaciteMin" type="range" min="1" max="30"
                       class="cw-input" style="padding:.5rem 0">
            </div>
            <div class="cw-field">
                <label>{{ __('messages.date_debut') }}</label>
                <input wire:model.live="debut" type="datetime-local" class="cw-input">
            </div>
            <div class="cw-field">
                <label>{{ __('messages.date_fin') }}</label>
                <input wire:model.live="fin" type="datetime-local" class="cw-input">
            </div>
            <div class="cw-field" style="justify-content:flex-end">
                <button wire:click="resetFiltres" class="cw-btn cw-btn-outline cw-btn-sm">
                    <i class="fas fa-redo"></i> {{ __('messages.reinitialiser') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Loading --}}
    <div wire:loading class="cw-empty" style="padding:2rem">
        <i class="fas fa-spinner fa-spin" style="color:var(--primary);font-size:2rem"></i>
    </div>

    {{-- Grille espaces --}}
    <div wire:loading.remove>
        @if($espaces->isEmpty())
            <div class="cw-empty">
                <i class="fas fa-search"></i>
                <h3>{{ __('messages.aucun_espace') }}</h3>
                <button wire:click="resetFiltres" class="cw-btn cw-btn-outline" style="margin-top:1rem">
                    {{ __('messages.reinitialiser') }}
                </button>
            </div>
        @else
            <div class="cw-grid">
                @foreach($espaces as $espace)
                <div class="cw-card">
                    {{-- Image principale --}}
                    <div class="cw-card-img" style="background:var(--gradient);overflow:hidden">
                        <img src="{{ $espace->photo_url }}"
                             alt="{{ $espace->nom }}"
                             style="width:100%;height:100%;object-fit:cover"
                             onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $espace->id }}/400/300'">
                        <span class="cw-card-badge">{{ $espace->type_label }}</span>
                    </div>

                    <div class="cw-card-body">
                        <div style="display:flex;justify-content:space-between;align-items:start">
                            <div class="cw-card-title">{{ $espace->nom_localised }}</div>
                            @auth
                            <button wire:click="toggleFavori({{ $espace->id }})"
                                    class="cw-fav-btn {{ in_array($espace->id, $favorisIds) ? 'active' : '' }}"
                                    title="{{ in_array($espace->id, $favorisIds) ? __('messages.retirer_favoris') : __('messages.ajouter_favoris') }}">
                                <i class="{{ in_array($espace->id, $favorisIds) ? 'fas' : 'far' }} fa-heart"></i>
                            </button>
                            @endauth
                        </div>

                        <div class="cw-card-meta">
                            <span>
                                <i class="fas fa-users"></i>
                                {{ $espace->capacite_min }}-{{ $espace->capacite_max }} {{ __('messages.personnes_max') }}
                            </span>
                            @if($espace->avis_avg_note)
                                <span class="cw-card-rating">
                                    <span class="stars">★</span> {{ number_format($espace->avis_avg_note, 1) }}
                                    <span style="color:var(--gray-400)">({{ $espace->avis_count }})</span>
                                </span>
                            @else
                                <span style="color:var(--gray-400);font-size:.8rem">{{ __('messages.aucun_avis') }}</span>
                            @endif
                        </div>

                        @if($espace->description_localised)
                            <p style="font-size:.85rem;color:var(--gray-500);margin-top:.25rem">
                                {{ Str::limit($espace->description_localised, 80) }}
                            </p>
                        @endif

                        <div class="cw-occ-bar" title="{{ __('messages.taux_occupation') }}: {{ $espace->taux_occupation }}%">
                            <div class="cw-occ-fill" style="width:{{ $espace->taux_occupation }}%"></div>
                        </div>

                        <div class="cw-card-price">
                            {{ number_format($espace->prix_heure, 0) }}MAD
                            <small style="font-size:.65em;opacity:.7">{{ __('messages.par_heure') }}</small>
                            @if($espace->prix_journee)
                                <small style="font-size:.65em;opacity:.6;margin-left:.5rem">
                                    | {{ number_format($espace->prix_journee, 0) }}MAD/j
                                </small>
                            @endif
                        </div>

                        <div class="cw-card-actions">
                            <a wire:navigate href="{{ route('espaces.show', $espace) }}" class="cw-btn cw-btn-primary cw-btn-sm">
                                <i class="fas fa-calendar-plus"></i> {{ __('messages.reserver') }}
                            </a>
                            <a wire:navigate href="{{ route('espaces.show', $espace) }}" class="cw-btn cw-btn-outline cw-btn-sm">
                                {{ __('messages.voir_detail') }}
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

   {{-- Pagination avec boutons colorés --}}
@if(!$espaces->isEmpty() && $espaces->lastPage() > 1)
    <div class="cw-pagination">
        {{-- Bouton Précédent --}}
        @if($espaces->onFirstPage())
            <span class="cw-page-btn cw-page-btn-prev disabled" aria-disabled="true">
                <i class="fas fa-chevron-left"></i>
                <span>{{ app()->getLocale() === 'en' ? 'Previous' : 'Précédent' }}</span>
            </span>
        @else
            <a href="{{ $espaces->previousPageUrl() }}"
               wire:navigate
               class="cw-page-btn cw-page-btn-prev"
               aria-label="{{ app()->getLocale() === 'en' ? 'Previous page' : 'Page précédente' }}">
                <i class="fas fa-chevron-left"></i>
                <span>{{ app()->getLocale() === 'en' ? 'Previous' : 'Précédent' }}</span>
            </a>
        @endif

        {{-- Numéros de pages --}}
        <div class="cw-page-numbers">
            @php
                $currentPage = $espaces->currentPage();
                $lastPage = $espaces->lastPage();
                $range = 2;
            @endphp

            {{-- Première page --}}
            @if($currentPage > $range + 1)
                <a href="{{ $espaces->url(1) }}" 
                   wire:navigate 
                   class="cw-page-btn cw-page-btn-number"
                   aria-label="{{ __('messages.page') }} 1">
                    1
                </a>
                @if($currentPage > $range + 2)
                    <span class="cw-page-ellipsis">…</span>
                @endif
            @endif

            {{-- Pages autour de la page courante --}}
            @for($page = max(1, $currentPage - $range); $page <= min($lastPage, $currentPage + $range); $page++)
                @if($page == $currentPage)
                    <span class="cw-page-btn cw-page-btn-number active" 
                          aria-current="page"
                          aria-label="{{ __('messages.page') }} {{ $page }}, {{ __('messages.page_actuelle') }}">
                        {{ $page }}
                        <span class="cw-page-active-indicator"></span>
                    </span>
                @else
                    <a href="{{ $espaces->url($page) }}"
                       wire:navigate
                       class="cw-page-btn cw-page-btn-number"
                       aria-label="{{ __('messages.page') }} {{ $page }}">
                        {{ $page }}
                    </a>
                @endif
            @endfor

            {{-- Dernière page --}}
            @if($currentPage < $lastPage - $range)
                @if($currentPage < $lastPage - $range - 1)
                    <span class="cw-page-ellipsis">…</span>
                @endif
                <a href="{{ $espaces->url($lastPage) }}"
                   wire:navigate
                   class="cw-page-btn cw-page-btn-number"
                   aria-label="{{ __('messages.page') }} {{ $lastPage }}">
                    {{ $lastPage }}
                </a>
            @endif
        </div>

        {{-- Bouton Suivant --}}
        @if($espaces->hasMorePages())
            <a href="{{ $espaces->nextPageUrl() }}"
               wire:navigate
               class="cw-page-btn cw-page-btn-next"
               aria-label="{{ app()->getLocale() === 'en' ? 'Next page' : 'Page suivante' }}">
                <span>{{ app()->getLocale() === 'en' ? 'Next' : 'Suivant' }}</span>
                <i class="fas fa-chevron-right"></i>
            </a>
        @else
            <span class="cw-page-btn cw-page-btn-next disabled" aria-disabled="true">
                <span>{{ app()->getLocale() === 'en' ? 'Next' : 'Suivant' }}</span>
                <i class="fas fa-chevron-right"></i>
            </span>
        @endif
    </div>
@endif
</div>