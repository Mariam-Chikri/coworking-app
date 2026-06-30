@extends('layouts.app')

@section('title', __('messages.hero_titre') . ' — CoWork')

@section('content')
<!-- Hero -->
<section class="cw-hero">
    <div class="cw-container">
        <div class="cw-hero-content">
            <h1>{{ __('messages.hero_titre') }}</h1>
            <p>{{ __('messages.hero_sous_titre') }}</p>
            <div class="cw-hero-actions">
                <a href="{{ route('espaces.index') }}" wire:navigate class="cw-btn-hero">
                    <i class="fas fa-th-large"></i> {{ __('messages.hero_cta') }}
                </a>
                <a href="#comment" class="cw-btn-hero-outline">
                    {{ __('messages.hero_cta2') }} <i class="fas fa-arrow-down"></i>
                </a>
            </div>
            <div class="cw-hero-stats">
                <div class="cw-hero-stat">
                    <strong>{{ \App\Models\Espace::reservable()->count() }}</strong>
                    <span>{{ __('messages.espaces_reservables') }}</span>
                </div>
                <div class="cw-hero-stat">
                    <strong>{{ \App\Models\User::count() }}+</strong>
                    <span>{{ app()->getLocale() === 'en' ? 'Members' : 'Membres' }}</span>
                </div>
                <div class="cw-hero-stat">
                    <strong>{{ \App\Models\Reservation::whereIn('statut', ['confirmee','terminee','prolongee'])->count() }}</strong>
                    <span>{{ app()->getLocale() === 'en' ? 'Bookings made' : 'Réservations effectuées' }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Comment ça marche -->
<section class="cw-section" id="comment">
    <div class="cw-container">
        <div class="cw-section-title">
            <h2>{{ __('messages.section_comment') }}</h2>
        </div>
        <div class="cw-steps">
            <div class="cw-step">
                <div class="cw-step-icon"><i class="fas fa-search"></i></div>
                <h3>{{ __('messages.etape1_titre') }}</h3>
                <p>{{ __('messages.etape1_desc') }}</p>
            </div>
            <div class="cw-step">
                <div class="cw-step-icon"><i class="fas fa-calendar-check"></i></div>
                <h3>{{ __('messages.etape2_titre') }}</h3>
                <p>{{ __('messages.etape2_desc') }}</p>
            </div>
            <div class="cw-step">
                <div class="cw-step-icon"><i class="fas fa-laptop"></i></div>
                <h3>{{ __('messages.etape3_titre') }}</h3>
                <p>{{ __('messages.etape3_desc') }}</p>
            </div>
        </div>
    </div>
</section>

<!-- Espaces à la une -->
<section class="cw-section cw-section-alt">
    <div class="cw-container">
        <div class="cw-section-title">
            <h2>{{ app()->getLocale() === 'en' ? 'Featured Spaces' : 'Espaces à la une' }}</h2>
        </div>
        <div class="cw-grid">
            @foreach(\App\Models\Espace::reservable()->withAvg('avis','note')->take(3)->get() as $espace)
            <div class="cw-card">
                <div class="cw-card-img" style="background:var(--gradient);overflow:hidden;padding:0">
                    <img src="{{ $espace->photo_url }}"
                         alt="{{ $espace->nom }}"
                         style="width:100%;height:100%;object-fit:cover"
                         onerror="this.onerror=null;this.src='https://picsum.photos/seed/{{ $espace->id }}/400/200'">
                    <span class="cw-card-badge">{{ $espace->type_label }}</span>
                </div>
                <div class="cw-card-body">
                    <div class="cw-card-title">{{ $espace->nom_localised }}</div>
                    <div class="cw-card-meta">
                        <span><i class="fas fa-users"></i> {{ $espace->capacite }} {{ __('messages.personnes_max') }}</span>
                        @if($espace->avis_avg_note)
                        <span class="cw-card-rating"><span class="stars">★</span> {{ number_format($espace->avis_avg_note,1) }}</span>
                        @endif
                    </div>
                    <div class="cw-card-price">{{ number_format($espace->prix_heure, 0) }}MAD <small style="font-size:.7em;opacity:.7">{{ __('messages.par_heure') }}</small></div>
                    <div class="cw-card-actions">
                        <a href="{{ route('espaces.show', $espace) }}" wire:navigate class="cw-btn cw-btn-primary cw-btn-sm">{{ __('messages.reserver') }}</a>
                        <a href="{{ route('espaces.show', $espace) }}" wire:navigate class="cw-btn cw-btn-outline cw-btn-sm">{{ __('messages.voir_detail') }}</a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div style="text-align:center;margin-top:2rem;">
            <a href="{{ route('espaces.index') }}" wire:navigate class="cw-btn cw-btn-primary">
                {{ app()->getLocale() === 'en' ? 'View all spaces' : 'Voir tous les espaces' }} <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- À propos — espaces non réservables -->
<section class="cw-section" id="a-propos">
    <div class="cw-container">
        <div class="cw-section-title">
            <h2>{{ __('messages.section_a_propos') }}</h2>
            <p>{{ app()->getLocale() === 'en' ? 'Spaces for everyone — bookable or just to enjoy' : 'Des espaces pour tous — réservables ou à profiter librement' }}</p>
        </div>
        <div class="cw-about-grid">
            <div class="cw-about-card">
                <div class="cw-about-card-img" style="overflow:hidden;padding:0;background:#f3e8d7">
                    <img src="{{ asset('images/cafe.png') }}"
                         style="width:100%;height:100%;object-fit:cover;display:block"
                         alt="{{ __('messages.coin_cafe_nom') }}"
                         onerror="this.style.display='none';this.parentElement.style.display='flex';this.parentElement.style.alignItems='center';this.parentElement.style.justifyContent='center';this.parentElement.innerHTML='☕'">
                </div>
                <div class="cw-about-card-body">
                    <h3>{{ __('messages.coin_cafe_nom') }}</h3>
                    <p>{{ __('messages.coin_cafe_desc') }}</p>
                    <span class="cw-pill" style="background:#fff7ed;color:#c2410c;margin-top:.75rem;">
                        <i class="fas fa-info-circle"></i> {{ app()->getLocale() === 'en' ? 'Free access' : 'Accès libre' }}
                    </span>
                </div>
            </div>
            <div class="cw-about-card">
                <div class="cw-about-card-img" style="overflow:hidden;padding:0;background:#d1f0e0">
                    <img src="{{ asset('images/terrasse.png') }}"
                         style="width:100%;height:100%;object-fit:cover;display:block"
                         alt="{{ __('messages.terrasse_nom') }}"
                         onerror="this.style.display='none';this.parentElement.style.display='flex';this.parentElement.style.alignItems='center';this.parentElement.style.justifyContent='center';this.parentElement.innerHTML='🌿'">
                </div>
                <div class="cw-about-card-body">
                    <h3>{{ __('messages.terrasse_nom') }}</h3>
                    <p>{{ __('messages.terrasse_desc') }}</p>
                    <span class="cw-pill" style="background:#f0fdf4;color:#166534;margin-top:.75rem;">
                        <i class="fas fa-sun"></i> {{ app()->getLocale() === 'en' ? 'Seasonal' : 'Saisonnier' }}
                    </span>
                </div>
            </div>
            <div class="cw-about-card">
                <div class="cw-about-card-img" style="overflow:hidden;padding:0;background:#e8d7f3">
                    <img src="{{ asset('images/sieste.png') }}"
                         style="width:100%;height:100%;object-fit:cover;display:block"
                         alt="{{ __('messages.salon_nom') }}"
                         onerror="this.style.display='none';this.parentElement.style.display='flex';this.parentElement.style.alignItems='center';this.parentElement.style.justifyContent='center';this.parentElement.innerHTML='🛋️'">
                </div>
                <div class="cw-about-card-body">
                    <h3>{{ __('messages.salon_nom') }}</h3>
                    <p>{{ __('messages.salon_desc') }}</p>
                    <span class="cw-pill" style="background:#faf5ff;color:#6b21a8;margin-top:.75rem;">
                        <i class="fas fa-couch"></i> {{ app()->getLocale() === 'en' ? 'Relaxation' : 'Détente' }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA final -->
<section class="cw-section" style="background:var(--gradient);padding:4rem 0;">
    <div class="cw-container" style="text-align:center;color:white;">
        <h2 style="font-size:2rem;font-weight:800;margin-bottom:1rem;">
            {{ app()->getLocale() === 'en' ? 'Ready to start?' : 'Prêt à commencer ?' }}
        </h2>
        <p style="opacity:.85;margin-bottom:2rem;font-size:1.1rem;">
            {{ app()->getLocale() === 'en' ? 'Join hundreds of professionals who trust us.' : 'Rejoignez des centaines de professionnels qui nous font confiance.' }}
        </p>
        @guest
            <a href="{{ route('register') }}" wire:navigate class="cw-btn-hero">
                <i class="fas fa-rocket"></i> {{ __('messages.creer_compte') }}
            </a>
        @else
            <a href="{{ route('espaces.index') }}" wire:navigate class="cw-btn-hero">
                <i class="fas fa-th-large"></i> {{ __('messages.hero_cta') }}
            </a>
        @endguest
    </div>
</section>
@endsection


