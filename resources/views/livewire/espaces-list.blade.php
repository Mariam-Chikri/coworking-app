<div>
    {{-- Filtres --}}
    <div class="cw-filters">
        <div class="cw-filters-grid">
            <div class="cw-field">
                <label>{{ __('messages.rechercher') }}</label>
                <input wire:model.live.debounce.400ms="recherche" type="text" class="cw-input" placeholder="{{ __('messages.rechercher') }}">
            </div>
            <div class="cw-field">
                <label>{{ __('messages.type_espace') }}</label>
                <select wire:model.live="type" class="cw-select">
                    <option value="">{{ __('messages.tous_types') }}</option>
                    <option value="bureau">{{ __('messages.bureau') }}</option>
                    <option value="salle">{{ __('messages.salle') }}</option>
                    <option value="open_space">{{ __('messages.open_space') }}</option>
                </select>
            </div>
            <div class="cw-field">
                <label>{{ __('messages.capacite_min') }} ({{ $capaciteMin }})</label>
                <input wire:model.live="capaciteMin" type="range" min="1" max="30" class="cw-input" style="padding:.5rem 0">
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
                    <div class="cw-card-img" style="background:{{ $espace->couleur ?? 'var(--gradient)' }}">
                        @if(is_array($espace->photos) && count($espace->photos))
                            <img src="{{ asset('storage/'.$espace->photos[0]) }}" style="width:100%;height:100%;object-fit:cover">
                        @else
                            <i class="fas fa-{{ $espace->icone ?? 'building' }}" style="font-size:3.5rem"></i>
                        @endif
                        <span class="cw-card-badge">{{ __('messages.'.$espace->type) }}</span>
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
                            <span><i class="fas fa-users"></i> {{ $espace->capacite }} {{ __('messages.personnes_max') }}</span>
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
                            {{ number_format($espace->prix_heure, 0) }}€
                            <small style="font-size:.65em;opacity:.7">{{ __('messages.par_heure') }}</small>
                        </div>
                        <div class="cw-card-actions">
                            <a href="{{ route('espaces.show', $espace) }}" class="cw-btn cw-btn-primary cw-btn-sm">
                                <i class="fas fa-calendar-plus"></i> {{ __('messages.reserver') }}
                            </a>
                            <a href="{{ route('espaces.show', $espace) }}" class="cw-btn cw-btn-outline cw-btn-sm">
                                {{ __('messages.voir_detail') }}
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="cw-pagination">{{ $espaces->links() }}</div>
        @endif
    </div>
</div>
