<div wire:poll.20s>
    @php
        $graceSecondsLeft = 0;
        if ($canProlong) {
            $graceEnd = $reservation->fin->copy()->addMinutes(5);
            $graceSecondsLeft = max(0, (int) now()->diffInSeconds($graceEnd, false));
        }
    @endphp

    @if(!$estTerminee && ($isEnCours || $canProlong))
    <div style="background:white;border-radius:var(--radius-lg);padding:1.5rem;box-shadow:var(--shadow-sm);margin-bottom:1.5rem">
        <div style="font-size:.85rem;font-weight:600;color:var(--gray-600);margin-bottom:1rem;border-bottom:2px solid var(--gray-100);padding-bottom:.75rem">
            <i class="fas fa-bolt" style="color:var(--primary)"></i>
            {{ app()->getLocale() === 'en' ? 'Available Actions' : 'Actions disponibles' }}
        </div>

        {{-- === LIBÉRER L'ESPACE (pendant la période de réservation) === --}}
        @if($canRelease)
        <div style="margin-bottom:.75rem">
            <div style="font-size:.78rem;color:var(--gray-500);margin-bottom:.4rem;font-style:italic">
                <i class="fas fa-info-circle"></i>
                {{ app()->getLocale() === 'en'
                    ? 'Your reservation is in progress. You can release the space early.'
                    : 'Votre réservation est en cours. Vous pouvez libérer l\'espace maintenant.' }}
            </div>
            <button wire:click="libererAnticipement"
                    wire:confirm="{{ app()->getLocale() === 'en' ? 'Release this space early? The price will be recalculated based on actual duration.' : 'Libérer cet espace anticipativement ? Le prix sera recalculé selon la durée réelle.' }}"
                    class="cw-btn cw-btn-success" style="width:100%;justify-content:center">
                🕊️ {{ __('messages.liberer_anticipement') }}
            </button>
        </div>
        @endif

        {{-- === PROLONGER (uniquement pendant les 5 min de grâce après expiration) === --}}
        @if($canProlong)
        <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.4);border-radius:var(--radius);padding:.85rem 1rem;margin-bottom:.75rem">
            <div style="display:flex;align-items:center;gap:.5rem;font-size:.82rem;color:#92400e;font-weight:600;margin-bottom:.35rem">
                <i class="fas fa-hourglass-half"></i>
                {{ app()->getLocale() === 'en' ? '⏳ Grace Period Active' : '⏳ Période de grâce active' }}
            </div>
            <div style="font-size:.78rem;color:#78350f">
                {{ app()->getLocale() === 'en'
                    ? 'Your reservation has ended. You have 5 minutes to extend it.'
                    : 'Votre réservation est terminée. Vous avez 5 minutes pour la prolonger.' }}
                @if($graceSecondsLeft > 0)
                    <br><strong>
                        {{ app()->getLocale() === 'en' ? 'Remaining:' : 'Temps restant :' }}
                        {{ gmdate('i:s', $graceSecondsLeft) }}
                    </strong>
                @endif
            </div>
        </div>
        <button wire:click="ouvrirProlongation"
                class="cw-btn cw-btn-warning" style="width:100%;justify-content:center">
            <i class="fas fa-clock"></i> {{ __('messages.prolonger') }}
        </button>
        @endif
    </div>
    @endif

    {{-- === MODAL PROLONGATION === --}}
    @if($showProlongModal)
    <div class="cw-modal-overlay" wire:click.self="fermerProlongation">
        <div class="cw-modal">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem">
                <h3 style="margin:0">⏰ {{ __('messages.prolonger') }}</h3>
                <button wire:click="fermerProlongation" style="background:none;border:none;cursor:pointer;color:var(--gray-500);font-size:1.2rem">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            @if($erreurProlongation)
                <div style="background:#fef2f2;border-left:4px solid var(--danger);color:#991b1b;padding:.75rem 1rem;border-radius:8px;display:flex;align-items:center;gap:.5rem;margin-bottom:1rem;font-size:.88rem">
                    <i class="fas fa-exclamation-circle"></i> {{ $erreurProlongation }}
                </div>
            @endif

            {{-- Vérification disponibilité --}}
            <div style="background:#eff6ff;border-left:4px solid var(--info);border-radius:8px;padding:.65rem 1rem;margin-bottom:1rem;font-size:.8rem;color:#1e40af">
                <i class="fas fa-shield-alt"></i>
                {{ app()->getLocale() === 'en'
                    ? 'Availability will be checked before confirming.'
                    : 'La disponibilité de l\'espace sera vérifiée avant confirmation.' }}
            </div>

            <div class="cw-field" style="margin:1rem 0">
                <label style="font-weight:600;margin-bottom:.4rem;display:block">
                    {{ __('messages.heures_supplementaires') }} :
                    <span style="color:var(--primary)">{{ $heuresProlongation }}h</span>
                </label>
                <input wire:model.live="heuresProlongation" type="range" min="1" max="8"
                       class="cw-input" style="padding:.5rem 0;cursor:pointer">
                <div style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--gray-400);margin-top:.2rem">
                    <span>1h</span><span>4h</span><span>8h</span>
                </div>
            </div>

            @php
                $prixSupp = $heuresProlongation * $reservation->espace->prix_heure;
                $nouvelleFin = $reservation->fin->copy()->addHours($heuresProlongation);
            @endphp
            <div class="cw-price-preview">
                <div style="font-size:.82rem;color:var(--gray-500)">
                    {{ app()->getLocale() === 'en' ? 'New end time' : 'Nouvelle heure de fin' }} :
                    <strong>{{ $nouvelleFin->format('H:i') }}</strong>
                </div>
                <div class="price">+{{ number_format($prixSupp, 2) }} MAD</div>
            </div>

            <div class="cw-modal-actions">
                <button wire:click="fermerProlongation" class="cw-btn cw-btn-outline">
                    {{ app()->getLocale() === 'en' ? 'Cancel' : 'Annuler' }}
                </button>
                <button wire:click="prolonger" class="cw-btn cw-btn-primary">
                    <i class="fas fa-clock"></i> {{ __('messages.prolonger') }}
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
