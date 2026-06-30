@extends('layouts.app')
@section('title', $espace->nom_localised)
@section('content')
<div class="cw-page-header">
    <div class="cw-container">
        <div style="display:flex;align-items:center;gap:1rem">
            <a href="{{ route('espaces.index') }}"
               wire:navigate
               style="color:rgba(255,255,255,.7);transition:var(--transition)"
               onmouseover="this.style.color='white'" onmouseout="this.style.color='rgba(255,255,255,.7)'">
                <i class="fas fa-arrow-left"></i> {{ __('messages.espaces') }}
            </a>
            <span style="opacity:.5">›</span>
            <span>{{ $espace->nom_localised }}</span>
        </div>
        <h1 style="margin-top:.5rem">{{ $espace->nom_localised }}</h1>
    </div>
</div>

<div class="cw-container" style="padding-bottom:4rem">
    <div style="display:grid;grid-template-columns:1fr 380px;gap:2rem;align-items:start">

        {{-- Détail espace --}}
        <div>
            {{-- Image principale --}}
            <div style="height:320px;border-radius:var(--radius-lg);margin-bottom:2rem;overflow:hidden;background:var(--gradient);display:flex;align-items:center;justify-content:center">
                <img src="{{ $espace->photo_url }}"
                     alt="{{ $espace->nom }}"
                     style="width:100%;height:100%;object-fit:cover"
                     onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $espace->id }}/800/320'">
            </div>

            {{-- Infos principales --}}
            <div style="background:white;border-radius:var(--radius-lg);padding:2rem;margin-bottom:1.5rem;box-shadow:var(--shadow-sm)">
                <div style="display:flex;gap:1.5rem;flex-wrap:wrap;margin-bottom:1.5rem">
                    <div style="display:flex;align-items:center;gap:.6rem;font-size:.95rem">
                        <i class="fas fa-users" style="color:var(--primary)"></i>
                        {{ $espace->capacite_min }}-{{ $espace->capacite_max }} {{ __('messages.personnes_max') }}
                    </div>
                    <div style="display:flex;align-items:center;gap:.6rem;font-size:.95rem">
                        <i class="fas fa-tag" style="color:var(--primary)"></i>
                        {{ $espace->type_label }}
                    </div>
                    <div style="display:flex;align-items:center;gap:.6rem;font-size:1.2rem;font-weight:800">
                        <span class="cw-text-gradient">{{ number_format($espace->prix_heure, 0) }}MAD</span>
                        <span style="font-size:.85rem;font-weight:400;color:var(--gray-500)">{{ __('messages.par_heure') }}</span>
                    </div>
                    @if($espace->prix_journee)
                    <div style="display:flex;align-items:center;gap:.4rem;font-size:.9rem;color:var(--gray-600)">
                        <i class="fas fa-sun" style="color:var(--primary)"></i>
                        {{ number_format($espace->prix_journee, 0) }}MAD/{{ app()->getLocale() === 'en' ? 'day' : 'jour' }}
                    </div>
                    @endif
                    @if($espace->prix_mois)
                    <div style="display:flex;align-items:center;gap:.4rem;font-size:.9rem;color:var(--gray-600)">
                        <i class="fas fa-calendar-alt" style="color:var(--primary)"></i>
                        {{ number_format($espace->prix_mois, 0) }}MAD/{{ app()->getLocale() === 'en' ? 'month' : 'mois' }}
                    </div>
                    @endif
                    @if($espace->avis()->where('valide', true)->count())
                    <div style="display:flex;align-items:center;gap:.4rem;color:#f59e0b;font-weight:600">
                        ★ {{ number_format($espace->notes_moyenne, 1) }}
                        <span style="color:var(--gray-400);font-weight:400;font-size:.85rem">
                            ({{ $espace->avis()->where('valide', true)->count() }} {{ app()->getLocale() === 'en' ? 'reviews' : 'avis' }})
                        </span>
                    </div>
                    @endif
                </div>

                @if($espace->adresse)
                <div style="display:flex;align-items:center;gap:.5rem;font-size:.9rem;color:var(--gray-500);margin-bottom:1rem">
                    <i class="fas fa-map-marker-alt" style="color:var(--primary)"></i>
                    {{ $espace->adresse }}
                </div>
                @endif

                <p style="color:var(--gray-500);line-height:1.7">{{ $espace->description_localised }}</p>

                {{-- Taux d'occupation --}}
                <div style="margin-top:1rem">
                    <div style="display:flex;justify-content:space-between;font-size:.85rem;font-weight:600;margin-bottom:.4rem">
                        <span>{{ __('messages.taux_occupation') }}</span>
                        <span>{{ $espace->taux_occupation }}%</span>
                    </div>
                    <div class="cw-occ-bar">
                        <div class="cw-occ-fill" style="width:{{ $espace->taux_occupation }}%"></div>
                    </div>
                </div>
            </div>

            {{-- ✅ Avis - Uniquement pour les espaces réservables --}}
            @if($espace->type !== 'non_reservable')
            <div style="background:white;border-radius:var(--radius-lg);padding:2rem;box-shadow:var(--shadow-sm)" id="avis">
                <h3 style="font-weight:700;margin-bottom:1.5rem">
                    <i class="fas fa-star" style="color:#f59e0b"></i>
                    {{ app()->getLocale() === 'en' ? 'Reviews' : 'Avis clients' }}
                </h3>

                @php $avisValides = $espace->avis()->where('valide', true)->with('user')->latest()->get(); @endphp

                @if($avisValides->isEmpty())
                    <div class="cw-empty" style="padding:2rem">
                        <i class="fas fa-star" style="font-size:2rem"></i>
                        <p style="margin-top:.5rem">{{ __('messages.aucun_avis') }}</p>
                    </div>
                @else
                    <div class="cw-avis-list">
                        @foreach($avisValides as $avis)
                        <div class="cw-avis-card">
                            <div class="cw-avis-header">
                                <div class="cw-avatar" style="width:36px;height:36px;font-size:.9rem">
                                    {{ strtoupper(substr($avis->user->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <strong style="font-size:.9rem">{{ $avis->user->name }}</strong>
                                    <div class="cw-avis-stars">
                                        {{ str_repeat('★', $avis->note) }}{{ str_repeat('☆', 5 - $avis->note) }}
                                    </div>
                                </div>
                                <span style="margin-left:auto;font-size:.78rem;color:var(--gray-400)">
                                    {{ $avis->created_at->diffForHumans() }}
                                </span>
                            </div>
                            @if($avis->titre)
                                <div class="cw-avis-titre">{{ $avis->titre }}</div>
                            @endif
                            @if($avis->commentaire)
                                <p class="cw-avis-texte">{{ $avis->commentaire }}</p>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @endif

                {{-- Formulaire avis (utilisateur ayant une réservation terminée sans avis) --}}
                @auth
                @php
                    $rezTerminee = auth()->user()->reservations()
                        ->where('espace_id', $espace->id)
                        ->where('statut', 'terminee')
                        ->whereDoesntHave('avis')
                        ->latest()->first();
                @endphp
                @if($rezTerminee)
                    @livewire('avis-component', ['espace' => $espace, 'reservation' => $rezTerminee])
                @endif
                @endauth
            </div>
            @endif
        </div>

        {{-- Formulaire réservation (sticky) --}}
        <div style="position:sticky;top:1.5rem">
            @livewire('reservation-form', ['espace' => $espace])
        </div>

    </div>
</div>
@endsection