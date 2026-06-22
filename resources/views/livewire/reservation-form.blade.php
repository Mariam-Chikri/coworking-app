<div>
    @if($confirme)
    {{-- Confirmation --}}
    <div class="cw-success-box">
        <i class="fas fa-check-circle" style="font-size:2.5rem;color:var(--success);display:block;margin-bottom:.75rem"></i>
        <h4>{{ __('messages.reservation_confirmee') }}</h4>
        <p style="color:var(--gray-500);margin:.5rem 0">
            {{ app()->getLocale() === 'en' ? 'You will receive a confirmation shortly.' : 'Votre réservation a bien été enregistrée.' }}
        </p>
        <div style="display:flex;gap:.75rem;justify-content:center;margin-top:1rem">
            <a href="{{ route('reservations.index') }}" class="cw-btn cw-btn-primary cw-btn-sm">
                <i class="fas fa-list"></i> {{ __('messages.mes_reservations') }}
            </a>
            <button wire:click="$set('confirme', false)" class="cw-btn cw-btn-outline cw-btn-sm">
                <i class="fas fa-plus"></i>
                {{ app()->getLocale() === 'en' ? 'New booking' : 'Nouvelle réservation' }}
            </button>
        </div>
    </div>

    @else
    {{-- Formulaire de réservation --}}
    <div class="cw-reservation-panel">
        <h3>
            <i class="fas fa-calendar-plus" style="color:var(--primary)"></i>
            {{ __('messages.nouvelle_reservation') }}
        </h3>

        {{-- Erreur générale --}}
        @error('general')
            <div class="cw-alert cw-alert-error" style="position:static;margin-bottom:1rem">
                <i class="fas fa-exclamation-circle"></i> {{ $message }}
            </div>
        @enderror

        <div style="display:flex;flex-direction:column;gap:1rem">

            {{-- Date début --}}
            <div class="cw-field">
                <label>{{ __('messages.date_debut') }}</label>
                <input wire:model.live="debut"
                       type="datetime-local"
                       class="cw-input"
                       min="{{ now()->format('Y-m-d\TH:i') }}">
                @error('debut')
                    <span style="color:var(--danger);font-size:.8rem">{{ $message }}</span>
                @enderror
            </div>

            {{-- Date fin --}}
            <div class="cw-field">
                <label>{{ __('messages.date_fin') }}</label>
                <input wire:model.live="fin"
                       type="datetime-local"
                       class="cw-input"
                       min="{{ now()->format('Y-m-d\TH:i') }}">
                @error('fin')
                    <span style="color:var(--danger);font-size:.8rem">{{ $message }}</span>
                @enderror
            </div>

            {{-- Indicateur disponibilité (temps réel via Livewire) --}}
            @if($debut && $fin)
            <div class="cw-disponibilite {{ $disponible ? 'ok' : 'non' }}">
                <i class="fas fa-{{ $disponible ? 'check-circle' : 'times-circle' }}"></i>
                {{ $disponible ? __('messages.disponible') : __('messages.indisponible') }}
            </div>
            @endif

            {{-- Nombre de personnes --}}
            <div class="cw-field">
                <label>
                    {{ __('messages.nombre_personnes') }}
                    ({{ __('messages.capacite_min') }} {{ $espace->capacite_min }} –
                     max. {{ $espace->capacite_max }})
                </label>
                <input wire:model="nombre_personnes"
                       type="number"
                       min="{{ $espace->capacite_min }}"
                       max="{{ $espace->capacite_max }}"
                       class="cw-input">
                @error('nombre_personnes')
                    <span style="color:var(--danger);font-size:.8rem">{{ $message }}</span>
                @enderror
            </div>

            {{-- Notes optionnelles --}}
            <div class="cw-field">
                <label>{{ __('messages.notes_optionnelles') }}</label>
                <textarea wire:model="notes"
                          class="cw-textarea"
                          rows="2"
                          placeholder="{{ __('messages.notes_optionnelles') }}"></textarea>
            </div>

            {{-- Prix estimé (calcul temps réel) --}}
            @if($prix_estime > 0)
            <div class="cw-price-preview">
                <div style="font-size:.82rem;color:var(--gray-500);margin-bottom:.25rem">
                    {{ __('messages.prix_estime') }}
                </div>
                <div class="price">{{ number_format($prix_estime, 2) }} MAD</div>
                <div style="font-size:.78rem;color:var(--gray-400)">
                    {{ app()->getLocale() === 'en' ? 'VAT 20% included' : 'TVA 20% incluse' }}
                </div>
            </div>
            @endif

            {{-- Bouton réserver (Livewire — pas de rechargement de page) --}}
            @auth
                <button wire:click="reserver"
                        class="cw-btn cw-btn-primary"
                        style="width:100%"
                        @if(!$disponible || !$debut || !$fin) disabled @endif>
                    <span wire:loading.remove wire:target="reserver">
                        <i class="fas fa-check"></i> {{ __('messages.confirmer_reservation') }}
                    </span>
                    <span wire:loading wire:target="reserver">
                        <i class="fas fa-spinner fa-spin"></i>
                        {{ app()->getLocale() === 'en' ? 'Processing…' : 'Traitement…' }}
                    </span>
                </button>
            @else
                <a href="{{ route('login') }}" class="cw-btn cw-btn-primary" style="width:100%;justify-content:center">
                    <i class="fas fa-sign-in-alt"></i> {{ __('messages.connexion') }}
                </a>
                <p style="font-size:.8rem;color:var(--gray-400);text-align:center;margin:0">
                    {{ app()->getLocale() === 'en' ? 'You must be logged in to book.' : 'Connectez-vous pour réserver.' }}
                </p>
            @endauth

        </div>
    </div>
    @endif
</div>

