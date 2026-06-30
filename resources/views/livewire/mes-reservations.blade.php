<div wire:poll.30s>
    {{-- Filtres --}}
    <div style="display:flex;gap:.6rem;margin-bottom:1.5rem;flex-wrap:wrap">
        @foreach([
            '' => (app()->getLocale()==='en' ? 'All' : 'Toutes'),
            'confirmee' => (app()->getLocale()==='en' ? 'Confirmed' : 'Confirmées'),
            'prolongee' => (app()->getLocale()==='en' ? 'Extended' : 'Prolongées'),
            'terminee' => (app()->getLocale()==='en' ? 'Completed' : 'Terminées'),
            'annulee' => (app()->getLocale()==='en' ? 'Cancelled' : 'Annulées'),
        ] as $val => $label)
        <button wire:click="$set('filtreStatut', '{{ $val }}')"
                class="cw-btn cw-btn-xs {{ $filtreStatut === $val ? 'cw-btn-primary' : 'cw-btn-outline' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    @if($reservations->isEmpty())
        <div class="cw-empty">
            <i class="fas fa-calendar-times"></i>
            <h3>{{ app()->getLocale() === 'en' ? 'No bookings found' : 'Aucune réservation trouvée' }}</h3>
            <a href="{{ route('espaces.index') }}" class="cw-btn cw-btn-primary" style="margin-top:1rem">
                {{ __('messages.hero_cta') }}
            </a>
        </div>
    @else
        <div class="cw-reservation-list">
            @foreach($reservations as $rez)
            @php
                $isEnCours  = $rez->isEnCours();
                $canProlong = $rez->canBeProlonged();
                $canRelease = $rez->canBeReleasedEarly();
                $estTerminee = in_array($rez->statut, ['terminee', 'annulee']);

                // Temps restant dans la période de grâce
                $graceSecondsLeft = 0;
                if ($canProlong) {
                    $graceEnd = $rez->fin->copy()->addMinutes(5);
                    $graceSecondsLeft = max(0, (int) now()->diffInSeconds($graceEnd, false));
                }
            @endphp
            <div class="cw-reservation-card {{ $rez->statut }}">
                <div>
                    {{-- En-tête : nom de l'espace + badges --}}
                    <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:.5rem;flex-wrap:wrap">
                        <a href="{{ route('reservations.show', $rez) }}"
                           style="font-weight:700;font-size:1.05rem;color:var(--gray-900);text-decoration:none;transition:var(--transition)"
                           onmouseover="this.style.color='var(--primary)'" onmouseout="this.style.color='var(--gray-900)'">
                            {{ $rez->espace->nom }}
                        </a>
                        <span class="cw-statut-badge {{ $rez->statut }}">{{ __('messages.'.$rez->statut) }}</span>

                        @if($rez->liberation_anticipee)
                            <span class="cw-pill" style="background:#f0fdf4;color:#166534;font-size:.72rem">🕊️ {{ app()->getLocale()==='en' ? 'Early release' : 'Libéré tôt' }}</span>
                        @endif
                        @if($rez->fin_initiale)
                            <span class="cw-pill" style="background:rgba(102,126,234,.1);color:var(--primary);font-size:.72rem">⏰ {{ app()->getLocale()==='en' ? 'Extended' : 'Prolongée' }}</span>
                        @endif
                        @if($isEnCours)
                            <span class="cw-pill" style="background:#dbeafe;color:#1e40af;font-size:.72rem">🟢 {{ app()->getLocale()==='en' ? 'In progress' : 'En cours' }}</span>
                        @endif
                        @if($canProlong)
                            <span class="cw-pill" style="background:#fef3c7;color:#92400e;font-size:.72rem;animation:pulse-badge 1.5s infinite">
                                ⏳ {{ app()->getLocale()==='en' ? 'Grace period' : 'Période de grâce' }}
                                @if($graceSecondsLeft > 0)
                                    — {{ gmdate('i:s', $graceSecondsLeft) }}
                                @endif
                            </span>
                        @endif
                    </div>

                    {{-- Dates --}}
                    <div class="cw-rez-dates">
                        <i class="fas fa-calendar"></i>
                        {{ $rez->debut->format('d/m/Y H:i') }} → {{ $rez->fin->format('H:i') }}
                        @if($rez->fin_initiale)
                            <span style="text-decoration:line-through;color:var(--gray-400);font-size:.78rem;margin-left:.4rem">
                                ({{ $rez->fin_initiale->format('H:i') }})
                            </span>
                        @endif
                        &nbsp;•&nbsp;<strong>{{ $rez->duree_heures }}h</strong>
                    </div>

                    {{-- Métadonnées --}}
                    <div class="cw-rez-dates">
                        <i class="fas fa-users"></i> {{ $rez->nombre_personnes }} {{ app()->getLocale()==='en' ? 'pers.' : 'pers.' }}
                        &nbsp;•&nbsp; <span style="font-family:monospace;font-size:.82rem">#{{ $rez->numero }}</span>
                    </div>

                    {{-- Notes --}}
                    @if($rez->notes)
                        <div style="font-size:.8rem;color:var(--gray-500);margin-top:.2rem;font-style:italic">
                            "{{ Str::limit($rez->notes, 80) }}"
                        </div>
                    @endif

                    {{-- Alerte période de grâce --}}
                    @if($canProlong)
                    <div style="margin-top:.6rem;background:#fef3c7;border-left:3px solid #f59e0b;border-radius:6px;padding:.5rem .75rem;font-size:.78rem;color:#78350f">
                        <i class="fas fa-hourglass-half"></i>
                        {{ app()->getLocale()==='en'
                            ? 'Reservation ended — you can extend it within the 5-minute grace period.'
                            : 'Réservation expirée — vous pouvez encore la prolonger dans la période de grâce.' }}
                    </div>
                    @endif
                </div>

                {{-- Actions --}}
                <div style="text-align:right;min-width:120px">
                    <div class="cw-rez-prix">{{ number_format($rez->prix_total, 2) }} MAD</div>
                    @if($rez->prix_prolongation > 0)
                        <div style="font-size:.75rem;color:var(--gray-400)">dont {{ number_format($rez->prix_prolongation,2) }} MAD prolong.</div>
                    @endif

                    <div class="cw-rez-actions" style="margin-top:.75rem">

                        {{-- Voir le détail --}}
                        <a href="{{ route('reservations.show', $rez) }}"
                           class="cw-btn cw-btn-outline cw-btn-xs"
                           title="{{ app()->getLocale()==='en' ? 'View details' : 'Voir le détail' }}">
                            <i class="fas fa-eye"></i>
                        </a>

                        {{-- Télécharger la facture --}}
                        @if($rez->facture)
                        <a href="{{ route('factures.pdf', $rez->facture) }}" target="_blank"
                           class="cw-btn cw-btn-outline cw-btn-xs" title="{{ __('messages.telecharger_pdf') }}">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        @endif

                        {{-- === LIBÉRER L'ESPACE — visible pendant toute la période de réservation === --}}
                        @if($canRelease)
                        <button wire:click="libererAnticipement({{ $rez->id }})"
                                wire:confirm="{{ app()->getLocale()==='en' ? 'Release this space early? Price will be recalculated.' : 'Libérer cet espace maintenant ? Le prix sera recalculé.' }}"
                                class="cw-btn cw-btn-success cw-btn-xs"
                                title="{{ __('messages.liberer_anticipement') }}">
                            🕊️ {{ __('messages.liberer_anticipement') }}
                        </button>
                        @endif

                        {{-- === PROLONGER — uniquement pendant les 5 min de grâce après expiration === --}}
                        @if($canProlong)
                        <button wire:click="ouvrirProlongation({{ $rez->id }})"
                                class="cw-btn cw-btn-warning cw-btn-xs"
                                title="{{ app()->getLocale()==='en' ? 'Extend booking' : 'Prolonger la réservation' }}">
                            <i class="fas fa-clock"></i> {{ __('messages.prolonger') }}
                        </button>
                        @endif

                        {{-- Annuler (avant le début uniquement) --}}
                        @if(in_array($rez->statut, ['en_attente','confirmee']) && $rez->debut->isFuture())
                        <button wire:click="confirmerAnnulation({{ $rez->id }})"
                                class="cw-btn cw-btn-danger cw-btn-xs"
                                title="{{ app()->getLocale()==='en' ? 'Cancel booking' : 'Annuler la réservation' }}">
                            <i class="fas fa-times"></i>
                        </button>
                        @endif

                        {{-- Donner un avis (réservation terminée, pas encore d'avis) --}}
                        @if($rez->statut === 'terminee' && !$rez->avis && $rez->espace->type !== 'non_reservable')
                        <a href="{{ route('espaces.show', $rez->espace) }}#avis"
                           class="cw-btn cw-btn-outline cw-btn-xs"
                           title="{{ app()->getLocale()==='en' ? 'Leave a review' : 'Donner un avis' }}">
                            <i class="fas fa-star"></i>
                        </a>
                        @endif

                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="cw-pagination">{{ $reservations->links() }}</div>
    @endif

    {{-- ====== MODAL ANNULATION ====== --}}
    @if($reservationAnnulerID)
    <div class="cw-modal-overlay" wire:click.self="$set('reservationAnnulerID', null)">
        <div class="cw-modal">
            <h3>{{ __('messages.confirmer_annulation') }}</h3>
            <p style="color:var(--gray-500);margin-top:.5rem">{{ __('messages.confirmer_annulation_msg') }}</p>
            <div class="cw-modal-actions">
                <button wire:click="$set('reservationAnnulerID', null)" class="cw-btn cw-btn-outline">
                    {{ app()->getLocale() === 'en' ? 'Back' : 'Retour' }}
                </button>
                <button wire:click="annuler" class="cw-btn cw-btn-danger">
                    <i class="fas fa-times"></i> {{ __('messages.annuler_reservation') }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ====== MODAL PROLONGATION ====== --}}
    @if($reservationProlongerID)
    @php $rezProlong = \App\Models\Reservation::find($reservationProlongerID); @endphp
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

            {{-- Info disponibilité --}}
            <div style="background:#eff6ff;border-left:4px solid var(--info);border-radius:8px;padding:.65rem 1rem;margin-bottom:1rem;font-size:.8rem;color:#1e40af">
                <i class="fas fa-shield-alt"></i>
                {{ app()->getLocale() === 'en'
                    ? 'Space availability will be verified before confirming the extension.'
                    : 'La disponibilité de l\'espace sera vérifiée avant de confirmer la prolongation.' }}
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

            @if($rezProlong)
            @php
                $prixSupp    = $heuresProlongation * $rezProlong->espace->prix_heure;
                $nouvelleFin = $rezProlong->fin->copy()->addHours($heuresProlongation);
            @endphp
            <div class="cw-price-preview">
                <div style="font-size:.82rem;color:var(--gray-500)">
                    {{ app()->getLocale() === 'en' ? 'New end time' : 'Nouvelle heure de fin' }} :
                    <strong>{{ $nouvelleFin->format('H:i') }}</strong>
                </div>
                <div class="price">+{{ number_format($prixSupp, 2) }} MAD</div>
            </div>
            @endif

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

