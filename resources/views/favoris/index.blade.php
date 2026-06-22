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
                <div class="cw-card-img" style="background:{{ $espace->couleur ?? 'var(--gradient)' }}">
                    <i class="fas fa-{{ $espace->icone ?? 'building' }}" style="font-size:3.5rem"></i>
                    <span class="cw-card-badge">{{ __('messages.'.$espace->type) }}</span>
                </div>
                <div class="cw-card-body">
                    <div class="cw-card-title">{{ $espace->nom_localised }}</div>
                    <div class="cw-card-meta">
                        <span><i class="fas fa-users"></i> {{ $espace->capacite }} {{ __('messages.personnes_max') }}</span>
                        @if($espace->avis_avg_note)
                        <span class="cw-card-rating"><span class="stars">★</span> {{ number_format($espace->avis_avg_note,1) }}</span>
                        @endif
                    </div>
                    <div class="cw-card-price">{{ number_format($espace->prix_heure, 0) }}MAD <small>{{ __('messages.par_heure') }}</small></div>
                    <div class="cw-card-actions">
                        <a wire:navigate href="{{ route('espaces.show', $espace) }}" class="cw-btn cw-btn-primary cw-btn-sm">
                                                    {{ __('messages.reserver') }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
