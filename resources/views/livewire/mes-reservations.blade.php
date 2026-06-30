<div class="cw-container" style="padding:2rem 0">
    <!-- ✅ FILTRES -->
    <div style="display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;align-items:center">
        <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
            <span style="font-weight:600;color:var(--gray-600)">{{ __('messages.filtrer_par') }}:</span>
            
            {{-- Bouton Tous --}}
            <button type="button"
                    wire:click="$set('filtreStatut', '')"
                    class="cw-btn {{ !$this->filtreStatut ? 'cw-btn-primary' : 'cw-btn-outline' }} cw-btn-sm"
                    style="cursor:pointer">
                <i class="fas fa-list"></i> {{ __('messages.tous') }}
            </button>

            {{-- Bouton Confirmée --}}
            <button type="button"
                    wire:click="$set('filtreStatut', 'confirmee')"
                    class="cw-btn {{ $this->filtreStatut === 'confirmee' ? 'cw-btn-primary' : 'cw-btn-outline' }} cw-btn-sm"
                    style="cursor:pointer">
                <i class="fas fa-check-circle"></i> {{ __('messages.confirmee') }}
            </button>

            {{-- Bouton Prolongée --}}
            <button type="button"
                    wire:click="$set('filtreStatut', 'prolongee')"
                    class="cw-btn {{ $this->filtreStatut === 'prolongee' ? 'cw-btn-primary' : 'cw-btn-outline' }} cw-btn-sm"
                    style="cursor:pointer">
                <i class="fas fa-clock"></i> {{ __('messages.prolongee') }}
            </button>

            {{-- Bouton Terminée --}}
            <button type="button"
                    wire:click="$set('filtreStatut', 'terminee')"
                    class="cw-btn {{ $this->filtreStatut === 'terminee' ? 'cw-btn-primary' : 'cw-btn-outline' }} cw-btn-sm"
                    style="cursor:pointer">
                <i class="fas fa-flag-checkered"></i> {{ __('messages.terminee') }}
            </button>

            {{-- Bouton Annulée --}}
            <button type="button"
                    wire:click="$set('filtreStatut', 'annulee')"
                    class="cw-btn {{ $this->filtreStatut === 'annulee' ? 'cw-btn-primary' : 'cw-btn-outline' }} cw-btn-sm"
                    style="cursor:pointer">
                <i class="fas fa-times-circle"></i> {{ __('messages.annulee') }}
            </button>
        </div>
    </div>

    {{-- ✅ RÉSERVATIONS LIST --}}
    @if($reservations->count() > 0)
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(320px, 1fr));gap:1.5rem;margin-bottom:2rem">
        @foreach($reservations as $reservation)
        <div class="cw-card" style="background:white;border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-sm);display:flex;flex-direction:column;transition:all .3s ease">
            {{-- Header Carte --}}
            <div style="padding:1.5rem;background:linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);color:white">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.5rem">
                    <div>
                        <div style="font-weight:700;font-size:1.1rem">{{ $reservation->espace->nom }}</div>
                        <div style="font-size:.85rem;opacity:.9">{{ $reservation->espace->type_label }}</div>
                    </div>
                    <span class="cw-statut-badge {{ $reservation->statut }}" style="padding:.3rem .6rem;font-size:.75rem;border-radius:4px;background:rgba(255,255,255,.2);border:1px solid rgba(255,255,255,.3)">
                        @switch($reservation->statut)
                            @case('confirmee')<i class="fas fa-check-circle"></i> @break
                            @case('prolongee')<i class="fas fa-clock"></i> @break
                            @case('terminee')<i class="fas fa-flag-checkered"></i> @break
                            @case('annulee')<i class="fas fa-times-circle"></i> @break
                        @endswitch
                        {{ __('messages.' . $reservation->statut) }}
                    </span>
                </div>
            </div>

            {{-- Body Carte --}}
            <div style="padding:1.5rem;flex:1">
                <div style="display:grid;gap:1rem">
                    {{-- Date Début --}}
                    <div style="display:flex;gap:.5rem;align-items:flex-start">
                        <i class="fas fa-calendar" style="color:var(--primary);margin-top:.2rem;flex-shrink:0"></i>
                        <div style="flex:1">
                            <div style="font-size:.75rem;font-weight:600;color:var(--gray-400);text-transform:uppercase">{{ __('messages.date_debut') }}</div>
                            <div style="font-size:.95rem;font-weight:600">{{ $reservation->debut->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>

                    {{-- Date Fin --}}
                    <div style="display:flex;gap:.5rem;align-items:flex-start">
                        <i class="fas fa-hourglass-end" style="color:var(--primary);margin-top:.2rem;flex-shrink:0"></i>
                        <div style="flex:1">
                            <div style="font-size:.75rem;font-weight:600;color:var(--gray-400);text-transform:uppercase">{{ __('messages.date_fin') }}</div>
                            <div style="font-size:.95rem;font-weight:600">{{ $reservation->fin->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>

                    {{-- Prix --}}
                    <div style="padding-top:.5rem;border-top:1px solid var(--gray-100)">
                        <div style="font-size:.75rem;font-weight:600;color:var(--gray-400);text-transform:uppercase;margin-bottom:.3rem">{{ __('messages.montant_ttc') }}</div>
                        <div style="font-size:1.4rem;font-weight:800;color:var(--primary)">{{ number_format($reservation->prix_total, 2) }} MAD</div>
                    </div>
                </div>
            </div>

            {{-- Footer Carte --}}
            <div style="padding:1.5rem;border-top:1px solid var(--gray-100);display:flex;gap:.75rem;flex-wrap:wrap">
                {{-- Bouton Voir Détails --}}
                <a href="{{ route('reservations.show', $reservation) }}" class="cw-btn cw-btn-primary cw-btn-sm" style="flex:1;text-align:center">
                    <i class="fas fa-eye"></i> {{ __('messages.voir_details') }}
                </a>

                {{-- Bouton Annuler (si possible) --}}
                @if(in_array($reservation->statut, ['en_attente', 'confirmee']) && !$reservation->debut->isPast())
                <button type="button"
                        wire:click="confirmerAnnulation({{ $reservation->id }})"
                        class="cw-btn cw-btn-danger cw-btn-sm"
                        style="flex:1;text-align:center;cursor:pointer">
                    <i class="fas fa-trash"></i> {{ __('messages.annuler') }}
                </button>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div style="text-align:center;padding:3rem 1.5rem">
        <i class="fas fa-inbox" style="font-size:4rem;color:var(--gray-300);display:block;margin-bottom:1rem"></i>
        <p style="color:var(--gray-500);font-size:1rem">{{ __('messages.aucune_reservation') }}</p>
    </div>
    @endif

    {{-- ✅ PAGINATION --}}
    @if($reservations->hasPages())
    <div style="display:flex;justify-content:center;gap:.5rem;flex-wrap:wrap">
        @if($reservations->onFirstPage())
            <button type="button" disabled class="cw-btn cw-btn-outline cw-btn-sm" style="opacity:.5">
                <i class="fas fa-chevron-left"></i> {{ __('messages.precedent') }}
            </button>
        @else
            <button type="button"
                    wire:click="previousPage"
                    class="cw-btn cw-btn-outline cw-btn-sm"
                    style="cursor:pointer">
                <i class="fas fa-chevron-left"></i> {{ __('messages.precedent') }}
            </button>
        @endif

        {{-- Numéros de pages --}}
        @foreach($reservations->getUrlRange(1, $reservations->lastPage()) as $page => $url)
            @if($page == $reservations->currentPage())
                <button type="button" disabled class="cw-btn cw-btn-primary cw-btn-sm" style="min-width:44px;cursor:default">
                    {{ $page }}
                </button>
            @else
                <button type="button"
                        wire:click="gotoPage({{ $page }})"
                        class="cw-btn cw-btn-outline cw-btn-sm"
                        style="min-width:44px;cursor:pointer">
                    {{ $page }}
                </button>
            @endif
        @endforeach

        @if($reservations->hasMorePages())
            <button type="button"
                    wire:click="nextPage"
                    class="cw-btn cw-btn-outline cw-btn-sm"
                    style="cursor:pointer">
                {{ __('messages.suivant') }} <i class="fas fa-chevron-right"></i>
            </button>
        @else
            <button type="button" disabled class="cw-btn cw-btn-outline cw-btn-sm" style="opacity:.5">
                {{ __('messages.suivant') }} <i class="fas fa-chevron-right"></i>
            </button>
        @endif
    </div>
    @endif

    {{-- ✅ MODAL ANNULATION --}}
    @if($reservationAnnulerID)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:999">
        <div style="background:white;border-radius:var(--radius-lg);padding:2rem;max-width:500px;width:90%;box-shadow:0 20px 25px -5px rgba(0,0,0,.1)">
            <h3 style="font-weight:700;margin-bottom:1rem"><i class="fas fa-warning" style="color:var(--danger)"></i> {{ __('messages.confirmer_annulation') }}</h3>
            <p style="color:var(--gray-600);margin-bottom:2rem">{{ __('messages.etes_vous_sur') }}</p>
            <div style="display:flex;gap:1rem;justify-content:flex-end">
                <button type="button" wire:click="$set('reservationAnnulerID', null)" class="cw-btn cw-btn-outline" style="cursor:pointer">{{ __('messages.non') }}</button>
                <button type="button" wire:click="annuler" class="cw-btn cw-btn-danger" style="cursor:pointer">{{ __('messages.oui_annuler') }}</button>
            </div>
        </div>
    </div>
    @endif

    {{-- ✅ MODAL PROLONGATION --}}
    @if($reservationProlongerID)
    <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:flex;align-items:center;justify-content:center;z-index:999">
        <div style="background:white;border-radius:var(--radius-lg);padding:2rem;max-width:500px;width:90%;box-shadow:0 20px 25px -5px rgba(0,0,0,.1)">
            <h3 style="font-weight:700;margin-bottom:1rem"><i class="fas fa-clock" style="color:var(--primary)"></i> {{ __('messages.prolonger_reservation') }}</h3>
            <p style="color:var(--gray-600);margin-bottom:1.5rem">{{ __('messages.combien_heures') }}</p>
            
            <input type="number" wire:model="heuresProlongation" min="1" max="24" class="cw-input" style="width:100%;margin-bottom:1rem;padding:.5rem;border:1px solid var(--gray-300);border-radius:4px">
            
            @if($erreurProlongation)
            <div style="background:#fee;color:#c33;padding:1rem;border-radius:4px;margin-bottom:1rem;font-size:.9rem">
                <i class="fas fa-exclamation-circle"></i> {{ $erreurProlongation }}
            </div>
            @endif
            
            <div style="display:flex;gap:1rem;justify-content:flex-end">
                <button type="button" wire:click="fermerProlongation" class="cw-btn cw-btn-outline" style="cursor:pointer">{{ __('messages.annuler') }}</button>
                <button type="button" wire:click="prolonger" class="cw-btn cw-btn-primary" style="cursor:pointer">{{ __('messages.confirmer') }}</button>
            </div>
        </div>
    </div>
    @endif
</div>
