@extends('layouts.app')
@section('title', __('messages.mes_favoris'))
@section('content')
<div class="cw-page-header">
    <div class="cw-container">
        <h1>❤️ {{ __('messages.mes_favoris') }}</h1>
        <p>{{ app()->getLocale() === 'en' ? 'Your saved spaces' : 'Vos espaces sauvegardés' }}</p>
    </div>
</div>
<div class="cw-container" style="padding-bottom:4rem">
    @php $favoris = auth()->user()->espacesFavoris()->withAvg('avis','note')->withCount('avis')->get(); @endphp
    @if($favoris->isEmpty())
        <div class="cw-empty">
            <i class="fas fa-heart"></i>
            <h3>{{ __('messages.aucun_favori') }}</h3>
            <a wire:navigate href="{{ route('espaces.index') }}" class="cw-btn cw-btn-primary" style="margin-top:1rem">
                {{ __('messages.hero_cta') }}
            </a>
        </div>
    @else
        <div class="cw-grid">
            @foreach($favoris as $espace)
            <div class="cw-card">
                {{-- ✅ Image avec photo_url --}}
                <div class="cw-card-img" style="background:{{ $espace->couleur ?? 'var(--gradient)' }};overflow:hidden;padding:0">
                    <img src="{{ $espace->photo_url }}"
                         alt="{{ $espace->nom_localised }}"
                         style="width:100%;height:100%;object-fit:cover"
                         onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $espace->id }}/400/300'">
                    <span class="cw-card-badge">{{ $espace->type_label }}</span>
                </div>
                <div class="cw-card-body">
                    <div style="display:flex;justify-content:space-between;align-items:start">
                        <div class="cw-card-title">{{ $espace->nom_localised }}</div>
                        {{-- Bouton pour retirer des favoris --}}
                        <button wire:click="toggleFavori({{ $espace->id }})"
                                class="cw-fav-btn active"
                                title="{{ __('messages.retirer_favoris') }}">
                            <i class="fas fa-heart"></i>
                        </button>
                    </div>
                    <div class="cw-card-meta">
                        <span>
                            <i class="fas fa-users"></i> 
                            {{ $espace->capacite_min ?? $espace->capacite ?? 1 }} - {{ $espace->capacite_max ?? $espace->capacite ?? 1 }} {{ __('messages.personnes_max') }}
                        </span>
                        @if($espace->avis_avg_note)
                        <span class="cw-card-rating">
                            <span class="stars">★</span> {{ number_format($espace->avis_avg_note,1) }}
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
                    <div class="cw-card-price">
                        @if($espace->type === 'non_reservable')
                            <span style="color:var(--gray-400)">{{ __('messages.non_reservable') }}</span>
                        @else
                            {{ number_format($espace->prix_heure, 0) }}MAD 
                            <small style="font-size:.65em;opacity:.7">{{ __('messages.par_heure') }}</small>
                            @if($espace->prix_journee)
                                <small style="font-size:.65em;opacity:.6;margin-left:.5rem">
                                    | {{ number_format($espace->prix_journee, 0) }}MAD/j
                                </small>
                            @endif
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
@endsection