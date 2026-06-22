<div>
    {{-- Filtres --}}
    <div style="display:flex;gap:1rem;margin-bottom:1.5rem;flex-wrap:wrap">
        @foreach([''=>'Toutes','en_attente'=>'En attente','confirmee'=>'Confirmées','terminee'=>'Terminées','prolongee'=>'Prolongées','annulee'=>'Annulées'] as $val => $label)
        <button wire:click="$set('filtreStatut', '{{ $val }}')"
                class="cw-btn cw-btn-xs {{ $filtreStatut === $val ? 'cw-btn-primary' : 'cw-btn-outline' }}">
            {{ __('messages.'.(empty($val)?'accueil':$val)) === __('messages.accueil') ? $label : __('messages.'.$val ?: 'tous_types') }}
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
            <div class="cw-reservation-card {{ $rez->statut }}">
                <div>
                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem">
                        <span class="cw-rez-espace">{{ $rez->espace->nom }}</span>
                        <span class="cw-statut-badge {{ $rez->statut }}">{{ __('messages.'.$rez->statut) }}</span>
                        @if($rez->liberation_anticipee)
                            <span class="cw-pill" style="background:#f0fdf4;color:#166534;font-size:.75rem">🕊️ Libéré tôt</span>
                        @endif
                        @if($rez->fin_initiale)
                            <span class="cw-pill" style="background:rgba(102,126,234,.1);color:var(--primary);font-size:.75rem">⏰ Prolongée</span>
                        @endif
                    </div>
                    <div class="cw-rez-dates">
                        <i class="fas fa-calendar"></i>
                        {{ $rez->debut->format('d/m/Y H:i') }} → {{ $rez->fin->format('H:i') }}
                        @if($rez->fin_initiale)
                            <span style="text-decoration:line-through;color:var(--gray-400);font-size:.8rem;margin-left:.5rem">
                                ({{ $rez->fin_initiale->format('H:i') }})
                            </span>
                        @endif
                        &nbsp;•&nbsp;{{ $rez->duree_heures }}h
                    </div>
                    <div class="cw-rez-dates">
                        <i class="fas fa-users"></i> {{ $rez->nombre_personnes }} pers.
                        &nbsp;•&nbsp; #{{ $rez->numero }}
                    </div>
                    @if($rez->notes)
                        <div style="font-size:.82rem;color:var(--gray-500);margin-top:.25rem;font-style:italic">
                            "{{ Str::limit($rez->notes, 80) }}"
                        </div>
                    @endif
                </div>
                <div style="text-align:right">
                    <div class="cw-rez-prix">{{ number_format($rez->prix_total, 2) }} MAD</div>
                    @if($rez->prix_prolongation > 0)
                        <div style="font-size:.78rem;color:var(--gray-400)">dont {{ number_format($rez->prix_prolongation,2) }}MAD prolongation</div>
                    @endif
                    <div class="cw-rez-actions" style="margin-top:.75rem">
                        {{-- Facture --}}
                        @if($rez->facture)
                        <a href="{{ route('factures.pdf', $rez->facture) }}" target="_blank"
                           class="cw-btn cw-btn-outline cw-btn-xs" title="{{ __('messages.telecharger_pdf') }}">
                            <i class="fas fa-file-pdf"></i>
                        </a>
                        @endif

                        {{-- Prolonger --}}
                        @if($rez->isEnCours())
                        <button wire:click="ouvriProlongation({{ $rez->id }})"
                                class="cw-btn cw-btn-warning cw-btn-xs">
                            <i class="fas fa-clock"></i> {{ __('messages.prolonger') }}
                        </button>
                        <button wire:click="libererAnticipement({{ $rez->id }})"
                                class="cw-btn cw-btn-success cw-btn-xs"
                                onclick="return confirm('{{ __('messages.liberer_anticipement') }} ?')">
                            🕊️
                        </button>
                        @endif

                        {{-- Annuler --}}
                        @if(in_array($rez->statut, ['en_attente','confirmee']) && !$rez->debut->isPast())
                        <button wire:click="confirmerAnnulation({{ $rez->id }})"
                                class="cw-btn cw-btn-danger cw-btn-xs">
                            <i class="fas fa-times"></i>
                        </button>
                        @endif

                        {{-- Avis --}}
                        @if($rez->statut === 'terminee' && !$rez->avis)
                        <a href="{{ route('espaces.show', $rez->espace) }}#avis"
                           class="cw-btn cw-btn-outline cw-btn-xs">
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

    {{-- Modal annulation --}}
    @if($reservationAnnulerID)
    <div class="cw-modal-overlay" wire:click.self="$set('reservationAnnulerID', null)">
        <div class="cw-modal">
            <h3>{{ __('messages.confirmer_annulation') }}</h3>
            <p>{{ __('messages.confirmer_annulation_msg') }}</p>
            <div class="cw-modal-actions">
                <button wire:click="$set('reservationAnnulerID', null)" class="cw-btn cw-btn-outline">
                    {{ app()->getLocale() === 'en' ? 'Cancel' : 'Retour' }}
                </button>
                <button wire:click="annuler" class="cw-btn cw-btn-danger">
                    <i class="fas fa-times"></i> {{ __('messages.annuler_reservation') }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal prolongation --}}
    @if($reservationProlongerID)
    <div class="cw-modal-overlay" wire:click.self="$set('reservationProlongerID', null)">
        <div class="cw-modal">
            <h3>⏰ {{ __('messages.prolonger') }}</h3>
            <div class="cw-field" style="margin:1rem 0">
                <label>{{ __('messages.heures_supplementaires') }} : {{ $heuresProlongation }}h</label>
                <input wire:model.live="heuresProlongation" type="range" min="1" max="8" class="cw-input" style="padding:.5rem 0">
            </div>
            @php
                $rez = \App\Models\Reservation::find($reservationProlongerID);
                $prixSupp = $rez ? $heuresProlongation * $rez->espace->prix_heure : 0;
            @endphp
            <div class="cw-price-preview">
                <div style="font-size:.85rem;color:var(--gray-500)">Supplément</div>
                <div class="price">+{{ number_format($prixSupp, 2) }} MAD</div>
            </div>
            <div class="cw-modal-actions">
                <button wire:click="$set('reservationProlongerID', null)" class="cw-btn cw-btn-outline">
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
