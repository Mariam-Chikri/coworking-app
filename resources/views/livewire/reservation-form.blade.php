<div>
    @if($confirme)
    {{-- Confirmation --}}
    <div class="cw-success-box" style="text-align:center">
        <i class="fas fa-check-circle" style="font-size:3rem;color:var(--success);display:block;margin-bottom:.75rem"></i>
        <h4>{{ __('messages.reservation_confirmee') }}</h4>
        <p style="color:var(--gray-500);margin:.5rem 0">
            {{ app()->getLocale() === 'en'
                ? 'Your booking has been registered. A confirmation has been generated.'
                : 'Votre réservation est confirmée. Une facture a été générée.' }}
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

        {{-- ✅ AFFICHAGE DES ERREURS DE DISPONIBILITÉ --}}
        @if($erreurMessage && !$confirme)
            <div class="cw-alert cw-alert-error" style="position:static;margin-bottom:1rem">
                <i class="fas fa-exclamation-circle"></i> {{ $erreurMessage }}
            </div>
        @endif

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

            {{-- ✅ Indicateur de disponibilité --}}
            @if($debut && $fin)
                @if($erreurMessage)
                    <div class="cw-disponibilite non">
                        <i class="fas fa-times-circle"></i>
                        <strong>
                            @if(strpos($erreurMessage, 'bureau') !== false || strpos($erreurMessage, 'desk') !== false)
                                {{ $erreurMessage }}
                            @else
                                {{ __('messages.indisponible') }}
                            @endif
                        </strong>
                    </div>
                @else
                    <div class="cw-disponibilite ok">
                        <i class="fas fa-check-circle"></i>
                        {{ __('messages.disponible') }}
                    </div>
                @endif
            @endif

            {{-- Sélection du bureau (Open Space Créatif uniquement) --}}
            @if($espace->type === 'open_space_creatif')
            <div class="cw-field" style="background:#f0f9ff;border-left:3px solid #0ea5e9;padding:.75rem 1rem;border-radius:.5rem">
                <label style="color:#0369a1;font-weight:600">
                    <i class="fas fa-chair"></i>
                    {{ __('messages.numero_bureau') }} *
                </label>

                @if(!$debut || !$fin)
                    <p style="color:#64748b;font-size:.85rem;margin:.25rem 0 0">
                        <i class="fas fa-info-circle"></i>
                        {{ app()->getLocale() === 'en'
                            ? 'Select a date range first to see available desks.'
                            : 'Choisissez d\'abord un créneau pour voir les bureaux disponibles.' }}
                    </p>
                @elseif(count($bureauxDisponibles) === 0)
                    <div class="cw-disponibilite non" style="margin-top:.5rem">
                        <i class="fas fa-times-circle"></i>
                        {{ app()->getLocale() === 'en'
                            ? 'No desks available for this time slot.'
                            : 'Aucun bureau disponible sur ce créneau.' }}
                    </div>
                @else
                    <div style="display:flex;align-items:center;gap:.5rem;margin-bottom:.35rem">
                        <i class="fas fa-check-circle" style="color:var(--success)"></i>
                        <span style="font-size:.82rem;color:var(--gray-500)">
                            {{ count($bureauxDisponibles) }} {{ app()->getLocale() === 'en' ? 'desks available' : 'bureaux disponibles' }}
                        </span>
                    </div>
                    <select wire:model.live="numeroBureau" class="cw-input @error('numeroBureau') is-invalid @enderror">
                        <option value="">{{ app()->getLocale() === 'en' ? 'Select a desk' : 'Choisir un bureau' }}</option>
                        @foreach($bureauxDisponibles as $num)
                            <option value="{{ $num }}">
                                {{ app()->getLocale() === 'en' ? 'Desk' : 'Bureau' }} {{ $num }}
                            </option>
                        @endforeach
                    </select>
                    @error('numeroBureau')
                        <span style="color:var(--danger);font-size:.8rem">{{ $message }}</span>
                    @enderror
                    
                    {{-- ✅ Message de sélection --}}
                    @if($numeroBureau)
                        <div style="color:var(--success);font-size:.8rem;margin-top:.35rem">
                            <i class="fas fa-check-circle"></i>
                            {{ app()->getLocale() === 'en' 
                                ? 'Desk ' . $numeroBureau . ' selected' 
                                : 'Bureau ' . $numeroBureau . ' sélectionné' }}
                        </div>
                    @endif
                @endif
            </div>
            @endif

            {{-- Nombre de personnes --}}
            <div class="cw-field">
                <label>
                    {{ __('messages.nombre_personnes') }}
                    ({{ __('messages.capacite_min') }} {{ $espace->capacite_min }} –
                     max. {{ $espace->capacite_max }})
                </label>
                <input wire:model.live="nombre_personnes"
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

            {{-- Prix estimé --}}
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

            {{-- Bouton réserver --}}
            @auth
                @php
                    // ✅ LOGIQUE CORRIGÉE
                    $isDisabled = false;
                    
                    // Vérifier les conditions d'erreur
                    if ($erreurMessage || !$debut || !$fin || !$disponible || $conflitUtilisateur) {
                        $isDisabled = true;
                    }
                    
                    // Pour Open Space, vérifier que le bureau est sélectionné
                    if ($espace->type === 'open_space_creatif') {
                        if (empty($numeroBureau)) {
                            $isDisabled = true;
                        }
                    }
                @endphp

                <button wire:click="reserver"
                        class="cw-btn cw-btn-primary"
                        style="width:100%"
                        {{ $isDisabled ? 'disabled' : '' }}>
                    <span wire:loading.remove wire:target="reserver">
                        <i class="fas fa-check"></i> {{ __('messages.confirmer_reservation') }}
                    </span>
                    <span wire:loading wire:target="reserver">
                        <i class="fas fa-spinner fa-spin"></i>
                        {{ app()->getLocale() === 'en' ? 'Processing…' : 'Traitement…' }}
                    </span>
                </button>

                {{-- ✅ Messages d'erreur améliorés --}}
                @if($erreurMessage)
                    <p style="font-size:.78rem;color:var(--danger);text-align:center;margin:0">
                        <i class="fas fa-exclamation-circle"></i>
                        {{ $erreurMessage }}
                    </p>
                @elseif($espace->type === 'open_space_creatif' && empty($numeroBureau) && $debut && $fin && $disponible && !$conflitUtilisateur)
                    <p style="font-size:.78rem;color:#f59e0b;text-align:center;margin:0;font-weight:600;">
                        <i class="fas fa-exclamation-triangle"></i>
                        {{ app()->getLocale() === 'en'
                            ? '⚠️ Please select a desk to confirm.'
                            : '⚠️ Veuillez sélectionner un bureau pour confirmer.' }}
                    </p>
                @elseif(!$disponible && !$erreurMessage && $debut && $fin)
                    <p style="font-size:.78rem;color:var(--danger);text-align:center;margin:0">
                        <i class="fas fa-lock"></i>
                        {{ app()->getLocale() === 'en'
                            ? 'Choose an available time slot to confirm.'
                            : 'Choisissez un créneau disponible pour confirmer.' }}
                    </p>
                @elseif($conflitUtilisateur && !$erreurMessage)
                    <p style="font-size:.78rem;color:var(--danger);text-align:center;margin:0">
                        <i class="fas fa-user-times"></i>
                        {{ app()->getLocale() === 'en'
                            ? 'You cannot have two simultaneous reservations.'
                            : 'Vous ne pouvez pas avoir deux réservations au même moment.' }}
                    </p>
                @endif
            @else
                <a href="{{ route('login') }}" class="cw-btn cw-btn-primary" style="width:100%;justify-content:center">
                    <i class="fas fa-sign-in-alt"></i> {{ __('messages.connexion') }}
                </a>
                <p style="font-size:.8rem;color:var(--gray-400);text-align:center;margin:0">
                    {{ app()->getLocale() === 'en' ? 'Sign in to book.' : 'Connectez-vous pour réserver.' }}
                </p>
            @endauth

        </div>
    </div>
    @endif
</div>

{{-- ✅ JavaScript de débogage (optionnel) --}}
@push('scripts')
<script>
document.addEventListener('livewire:updated', function() {
    // Vérifier la valeur de numeroBureau dans la console
    @this.on('updated', function() {
        console.log('Numéro bureau :', @this.numeroBureau);
    });
});
</script>
@endpush