@extends('layouts.app')
@section('title', __('messages.mes_reservations') . ' — ' . $reservation->numero)
@section('content')

<div class="cw-page-header">
    <div class="cw-container">
        <div style="display:flex;align-items:center;gap:1rem">
            <a href="{{ route('reservations.index') }}"
               style="color:rgba(255,255,255,.7);transition:var(--transition)"
               onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,.7)'">
                <i class="fas fa-arrow-left"></i> {{ __('messages.mes_reservations') }}
            </a>
            <span style="opacity:.5">›</span>
            <span>{{ $reservation->numero }}</span>
        </div>
        <h1 style="margin-top:.5rem">
            {{ app()->getLocale() === 'en' ? 'Reservation Details' : 'Détail de la réservation' }}
        </h1>
    </div>
</div>

<div class="cw-container" style="padding-bottom:4rem">
    <div style="display:grid;grid-template-columns:1fr 340px;gap:2rem;align-items:start">

        {{-- Informations principales --}}
        <div>

            {{-- Carte espace --}}
            <div style="background:white;border-radius:var(--radius-lg);padding:2rem;margin-bottom:1.5rem;box-shadow:var(--shadow-sm)">
                <div style="display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap">
                    <div style="width:80px;height:80px;border-radius:12px;overflow:hidden;flex-shrink:0;background:var(--gradient);display:flex;align-items:center;justify-content:center">
                        <img src="{{ $reservation->espace->photo_url }}"
                             alt="{{ $reservation->espace->nom }}"
                             style="width:100%;height:100%;object-fit:cover"
                             onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $reservation->espace->id }}/80/80'">
                    </div>
                    <div style="flex:1">
                        <div style="font-size:1.2rem;font-weight:700">{{ $reservation->espace->nom }}</div>
                        <div style="font-size:.9rem;color:var(--gray-500);margin-top:.25rem">
                            {{ $reservation->espace->type_label }}
                        </div>
                        @if($reservation->espace->adresse)
                        <div style="font-size:.85rem;color:var(--gray-400);margin-top:.2rem">
                            <i class="fas fa-map-marker-alt" style="color:var(--primary)"></i>
                            {{ $reservation->espace->adresse }}
                        </div>
                        @endif
                    </div>
                    <a href="{{ route('espaces.show', $reservation->espace) }}" class="cw-btn cw-btn-outline cw-btn-sm">
                        <i class="fas fa-external-link-alt"></i>
                        {{ app()->getLocale() === 'en' ? 'View space' : 'Voir l\'espace' }}
                    </a>
                </div>
            </div>

            {{-- Détails réservation --}}
            <div style="background:white;border-radius:var(--radius-lg);padding:2rem;margin-bottom:1.5rem;box-shadow:var(--shadow-sm)">
                <h3 style="font-weight:700;margin-bottom:1.5rem;font-size:1rem">
                    <i class="fas fa-calendar-alt" style="color:var(--primary)"></i>
                    {{ app()->getLocale() === 'en' ? 'Booking Details' : 'Détails de la réservation' }}
                </h3>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem">
                    <div>
                        <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--gray-400);margin-bottom:.3rem">
                            {{ __('messages.date_debut') }}
                        </div>
                        <div style="font-size:1rem;font-weight:600">
                            {{ $reservation->debut->format('d/m/Y à H:i') }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--gray-400);margin-bottom:.3rem">
                            {{ __('messages.date_fin') }}
                        </div>
                        <div style="font-size:1rem;font-weight:600">
                            {{ $reservation->fin->format('d/m/Y à H:i') }}
                        </div>
                    </div>
                    <div>
                        <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--gray-400);margin-bottom:.3rem">
                            {{ app()->getLocale() === 'en' ? 'Duration' : 'Durée' }}
                        </div>
                        <div style="font-size:1rem;font-weight:600">
                            {{ number_format($reservation->duree_heures, 1) }}h
                        </div>
                    </div>
                    <div>
                        <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--gray-400);margin-bottom:.3rem">
                            {{ __('messages.nombre_personnes') }}
                        </div>
                        <div style="font-size:1rem;font-weight:600">
                            {{ $reservation->nombre_personnes }}
                        </div>
                    </div>
                    @if($reservation->fin_initiale)
                    <div style="grid-column:span 2">
                        <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--gray-400);margin-bottom:.3rem">
                            {{ app()->getLocale() === 'en' ? 'Initial end date (before extension)' : 'Fin initiale (avant prolongation)' }}
                        </div>
                        <div style="font-size:.95rem;color:var(--gray-600)">
                            {{ $reservation->fin_initiale->format('d/m/Y à H:i') }}
                        </div>
                    </div>
                    @endif
                    @if($reservation->liberation_anticipee)
                    <div style="grid-column:span 2">
                        <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--gray-400);margin-bottom:.3rem">
                            {{ app()->getLocale() === 'en' ? 'Early release at' : 'Libéré anticipativement à' }}
                        </div>
                        <div style="font-size:.95rem;color:var(--warning)">
                            {{ $reservation->liberation_anticipee->format('d/m/Y à H:i') }}
                        </div>
                    </div>
                    @endif
                </div>

                @if($reservation->notes)
                <div style="margin-top:1.5rem;padding-top:1.5rem;border-top:1px solid var(--gray-100)">
                    <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--gray-400);margin-bottom:.4rem">
                        Notes
                    </div>
                    <p style="color:var(--gray-500);line-height:1.6;margin:0">{{ $reservation->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Facture associée --}}
            @if($reservation->facture)
            <div style="background:white;border-radius:var(--radius-lg);padding:2rem;box-shadow:var(--shadow-sm)">
                <h3 style="font-weight:700;margin-bottom:1.5rem;font-size:1rem">
                    <i class="fas fa-file-invoice" style="color:var(--primary)"></i>
                    {{ __('messages.facture') }} — {{ $reservation->facture->numero ?? '—' }}
                </h3>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.5rem">
                    <div>
                        <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--gray-400);margin-bottom:.3rem">
                            {{ __('messages.montant_ht') }}
                        </div>
                        <div style="font-size:1rem;font-weight:600">
                            {{ number_format($reservation->facture->montant_ht ?? 0, 2) }} MAD
                        </div>
                    </div>
                    <div>
                        <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--gray-400);margin-bottom:.3rem">
                            {{ __('messages.tva') }} (20%)
                        </div>
                        <div style="font-size:1rem;font-weight:600">
                            {{ number_format($reservation->facture->montant_tva ?? 0, 2) }} MAD
                        </div>
                    </div>
                    <div style="grid-column:span 2;padding-top:1rem;border-top:2px solid var(--gray-100)">
                        <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--gray-400);margin-bottom:.3rem">
                            {{ __('messages.montant_ttc') }}
                        </div>
                        <div style="font-size:1.4rem;font-weight:800;color:var(--primary)">
                            {{ number_format($reservation->facture->montant_ttc ?? 0, 2) }} MAD
                        </div>
                    </div>
                </div>
                <a href="{{ route('factures.pdf', $reservation->facture) }}"
                   class="cw-btn cw-btn-outline"
                   target="_blank">
                    <i class="fas fa-download"></i> {{ __('messages.telecharger_pdf') }}
                </a>
            </div>
            @endif
        </div>

        {{-- Panneau latéral --}}
        <div>
            {{-- Statut --}}
            <div style="background:white;border-radius:var(--radius-lg);padding:2rem;margin-bottom:1.5rem;box-shadow:var(--shadow-sm);text-align:center">
                <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--gray-400);margin-bottom:.75rem">
                    Statut
                </div>
                <span class="cw-statut-badge {{ $reservation->statut }}" style="font-size:1rem;padding:.5rem 1.5rem">
                    @switch($reservation->statut)
                        @case('en_attente') <i class="fas fa-clock"></i> @break
                        @case('confirmee') <i class="fas fa-check-circle"></i> @break
                        @case('prolongee') <i class="fas fa-clock"></i> @break
                        @case('terminee') <i class="fas fa-flag-checkered"></i> @break
                        @case('annulee') <i class="fas fa-times-circle"></i> @break
                    @endswitch
                    {{ __('messages.' . $reservation->statut) }}
                </span>
                <div style="font-size:.8rem;color:var(--gray-400);margin-top:.75rem">
                    N° {{ $reservation->numero }}
                </div>
            </div>

            {{-- Prix total --}}
            <div style="background:white;border-radius:var(--radius-lg);padding:2rem;margin-bottom:1.5rem;box-shadow:var(--shadow-sm);text-align:center">
                <div style="font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.04em;color:var(--gray-400);margin-bottom:.5rem">
                    {{ app()->getLocale() === 'en' ? 'Total amount' : 'Montant total' }}
                </div>
                <div style="font-size:2rem;font-weight:800" class="cw-text-gradient">
                    {{ number_format($reservation->prix_total, 2) }} MAD
                </div>
                @if($reservation->prix_prolongation > 0)
                <div style="font-size:.8rem;color:var(--gray-400);margin-top:.3rem">
                    dont {{ number_format($reservation->prix_prolongation, 2) }} MAD de prolongation
                </div>
                @endif
            </div>

            {{-- Actions selon statut --}}
            @if($reservation->statut === 'confirmee' || $reservation->statut === 'prolongee')
            <div style="background:white;border-radius:var(--radius-lg);padding:1.5rem;box-shadow:var(--shadow-sm)">
                <div style="font-size:.85rem;font-weight:600;color:var(--gray-600);margin-bottom:1rem">
                    {{ app()->getLocale() === 'en' ? 'Actions' : 'Actions disponibles' }}
                </div>
                @if(!$reservation->debut->isPast())
                <form method="POST" action="{{ route('reservations.index') }}" onsubmit="return confirm('{{ __('messages.confirmer_annulation_msg') }}')">
                    @csrf
                    <input type="hidden" name="_method" value="DELETE">
                    {{-- Note: L'annulation se fait via le composant Livewire mes-reservations --}}
                </form>
                <a href="{{ route('reservations.index') }}" class="cw-btn cw-btn-outline" style="width:100%;justify-content:center;margin-bottom:.75rem">
                    <i class="fas fa-list"></i> {{ __('messages.mes_reservations') }}
                </a>
                @endif
                <a href="{{ route('espaces.show', $reservation->espace) }}" class="cw-btn cw-btn-primary" style="width:100%;justify-content:center">
                    <i class="fas fa-calendar-plus"></i> {{ __('messages.nouvelle_reservation') }}
                </a>
            </div>
            @else
            <div style="text-align:center">
                <a href="{{ route('espaces.index') }}" class="cw-btn cw-btn-primary">
                    <i class="fas fa-search"></i>
                    {{ app()->getLocale() === 'en' ? 'Browse spaces' : 'Voir les espaces' }}
                </a>
            </div>
            @endif
        </div>

    </div>
</div>
@endsection

