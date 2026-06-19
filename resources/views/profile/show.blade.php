@extends('layouts.app')
@section('title', __('messages.mon_profil'))

@section('content')

@php
    $user = auth()->user();
    $locale = app()->getLocale();
    $totalRez = $user->reservations()->count();
    $rezConfirmees = $user->reservations()->where('statut', 'confirmee')->count();
    $totalFactures = $user->factures()->count();
    $totalDepense = $user->factures()->where('statut', 'payee')->sum('montant_ttc');
    $recentRez = $user->reservations()->with('espace')->latest()->take(5)->get();
    $recentFactures = $user->factures()->with('reservation.espace')->latest()->take(5)->get();
    $tab = request('tab', 'profil');
@endphp

{{-- ===== Page Header ===== --}}
<div class="cw-page-header">
    <div class="cw-container">
        <div style="display:flex;align-items:center;gap:1.5rem;flex-wrap:wrap">
            <div style="width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;color:white;border:3px solid rgba(255,255,255,.5);flex-shrink:0">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <div>
                <h1 style="margin-bottom:.2rem">{{ $user->name }}</h1>
                <p style="opacity:.85;margin:0">
                    {{ $user->email }}
                    @if($user->is_admin)
                        &nbsp;·&nbsp;
                        <span style="background:rgba(255,255,255,.25);padding:.2rem .75rem;border-radius:20px;font-size:.8rem;font-weight:700">
                            Admin
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

<div class="cw-container" style="padding-bottom:4rem">

    {{-- ===== KPI Cards ===== --}}
    <div class="cw-admin-grid" style="margin-bottom:2rem">
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-calendar-check"></i></div>
            <div>
                <div class="cw-kpi-value">{{ $totalRez }}</div>
                <div class="cw-kpi-label">{{ $locale === 'en' ? 'Total bookings' : 'Réservations totales' }}</div>
            </div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-check-circle"></i></div>
            <div>
                <div class="cw-kpi-value">{{ $rezConfirmees }}</div>
                <div class="cw-kpi-label">{{ $locale === 'en' ? 'Active bookings' : 'Réservations actives' }}</div>
            </div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-file-invoice"></i></div>
            <div>
                <div class="cw-kpi-value">{{ $totalFactures }}</div>
                <div class="cw-kpi-label">{{ $locale === 'en' ? 'Invoices' : 'Factures' }}</div>
            </div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9)"><i class="fas fa-euro-sign"></i></div>
            <div>
                <div class="cw-kpi-value">{{ number_format($totalDepense, 0) }}€</div>
                <div class="cw-kpi-label">{{ $locale === 'en' ? 'Total spent' : 'Total dépensé' }}</div>
            </div>
        </div>
    </div>

    {{-- ===== Onglets ===== --}}
    <div style="display:flex;gap:.5rem;margin-bottom:2rem;border-bottom:2px solid var(--gray-200);padding-bottom:0">
        @foreach([
            'profil'       => ['icon' => 'fa-user',         'fr' => 'Mon Profil',        'en' => 'My Profile'],
            'reservations' => ['icon' => 'fa-calendar-alt', 'fr' => 'Mes Réservations',  'en' => 'My Bookings'],
            'factures'     => ['icon' => 'fa-file-invoice', 'fr' => 'Mes Factures',      'en' => 'My Invoices'],
        ] as $key => $item)
        <a href="{{ route('profile') }}?tab={{ $key }}"
           style="display:flex;align-items:center;gap:.5rem;padding:.75rem 1.25rem;border-radius:8px 8px 0 0;font-size:.9rem;font-weight:600;transition:var(--transition);border-bottom:3px solid {{ $tab === $key ? 'var(--primary)' : 'transparent' }};color:{{ $tab === $key ? 'var(--primary)' : 'var(--gray-500)' }};background:{{ $tab === $key ? 'var(--gradient-soft)' : 'transparent' }};margin-bottom:-2px">
            <i class="fas {{ $item['icon'] }}"></i>
            {{ $locale === 'en' ? $item['en'] : $item['fr'] }}
        </a>
        @endforeach
    </div>

    {{-- ======================================================
         ONGLET 1 — MON PROFIL
    ====================================================== --}}
    @if($tab === 'profil')
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1.5rem;align-items:start">

        {{-- Informations personnelles --}}
        <div style="background:white;border-radius:var(--radius-lg);padding:2rem;box-shadow:var(--shadow-sm)">
            <h3 style="font-weight:700;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:2px solid var(--gray-100);display:flex;align-items:center;gap:.6rem">
                <i class="fas fa-id-card" style="color:var(--primary)"></i>
                {{ $locale === 'en' ? 'Personal information' : 'Informations personnelles' }}
            </h3>

            @if($errors->has('name') || $errors->has('email') || $errors->has('telephone') || $errors->has('entreprise'))
                <div class="cw-alert cw-alert-error" style="position:static;margin-bottom:1rem">
                    <i class="fas fa-exclamation-circle"></i>
                    @foreach($errors->only(['name','email','telephone','entreprise']) as $err)
                        {{ $err }}<br>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="cw-field" style="margin-bottom:1rem">
                    <label class="cw-field label" for="name">
                        <i class="fas fa-user" style="color:var(--primary)"></i>
                        {{ __('messages.nom') }}
                    </label>
                    <input type="text" id="name" name="name"
                           class="cw-input @error('name') cw-input-error @enderror"
                           value="{{ old('name', $user->name) }}" required autocomplete="name">
                </div>

                <div class="cw-field" style="margin-bottom:1rem">
                    <label for="email">
                        <i class="fas fa-envelope" style="color:var(--primary)"></i>
                        {{ __('messages.email') }}
                    </label>
                    <input type="email" id="email" name="email"
                           class="cw-input @error('email') cw-input-error @enderror"
                           value="{{ old('email', $user->email) }}" required autocomplete="email">
                </div>

                <div class="cw-field" style="margin-bottom:1rem">
                    <label for="telephone">
                        <i class="fas fa-phone" style="color:var(--primary)"></i>
                        {{ __('messages.telephone') }}
                    </label>
                    <input type="tel" id="telephone" name="telephone"
                           class="cw-input"
                           value="{{ old('telephone', $user->telephone) }}"
                           placeholder="{{ $locale === 'en' ? '+33 1 23 45 67 89' : '+33 1 23 45 67 89' }}">
                </div>

                <div class="cw-field" style="margin-bottom:1.5rem">
                    <label for="entreprise">
                        <i class="fas fa-building" style="color:var(--primary)"></i>
                        {{ __('messages.entreprise') }}
                    </label>
                    <input type="text" id="entreprise" name="entreprise"
                           class="cw-input"
                           value="{{ old('entreprise', $user->entreprise) }}"
                           placeholder="{{ $locale === 'en' ? 'Your company' : 'Votre entreprise' }}">
                </div>

                <button type="submit" class="cw-btn cw-btn-primary" style="width:100%;justify-content:center">
                    <i class="fas fa-save"></i>
                    {{ $locale === 'en' ? 'Save changes' : 'Enregistrer les modifications' }}
                </button>
            </form>
        </div>

        {{-- Changement de mot de passe --}}
        <div style="background:white;border-radius:var(--radius-lg);padding:2rem;box-shadow:var(--shadow-sm)">
            <h3 style="font-weight:700;margin-bottom:1.5rem;padding-bottom:1rem;border-bottom:2px solid var(--gray-100);display:flex;align-items:center;gap:.6rem">
                <i class="fas fa-lock" style="color:var(--primary)"></i>
                {{ $locale === 'en' ? 'Change password' : 'Changer le mot de passe' }}
            </h3>

            @if($errors->has('current_password') || $errors->has('password'))
                <div class="cw-alert cw-alert-error" style="position:static;margin-bottom:1rem">
                    <i class="fas fa-exclamation-circle"></i>
                    @foreach($errors->only(['current_password','password']) as $err)
                        {{ $err }}<br>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')

                <div class="cw-field" style="margin-bottom:1rem">
                    <label for="current_password">
                        <i class="fas fa-key" style="color:var(--primary)"></i>
                        {{ $locale === 'en' ? 'Current password' : 'Mot de passe actuel' }}
                    </label>
                    <input type="password" id="current_password" name="current_password"
                           class="cw-input @error('current_password') cw-input-error @enderror"
                           autocomplete="current-password">
                </div>

                <div class="cw-field" style="margin-bottom:1rem">
                    <label for="password">
                        <i class="fas fa-lock" style="color:var(--primary)"></i>
                        {{ $locale === 'en' ? 'New password' : 'Nouveau mot de passe' }}
                    </label>
                    <input type="password" id="password" name="password"
                           class="cw-input @error('password') cw-input-error @enderror"
                           autocomplete="new-password">
                </div>

                <div class="cw-field" style="margin-bottom:1.5rem">
                    <label for="password_confirmation">
                        <i class="fas fa-lock" style="color:var(--primary)"></i>
                        {{ __('messages.confirmer_mdp') }}
                    </label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="cw-input"
                           autocomplete="new-password">
                </div>

                <button type="submit" class="cw-btn cw-btn-outline" style="width:100%;justify-content:center">
                    <i class="fas fa-shield-alt"></i>
                    {{ $locale === 'en' ? 'Update password' : 'Mettre à jour le mot de passe' }}
                </button>
            </form>

            {{-- Infos supplémentaires --}}
            <div style="margin-top:2rem;padding-top:1.5rem;border-top:2px solid var(--gray-100)">
                <h4 style="font-size:.9rem;font-weight:600;color:var(--gray-700);margin-bottom:1rem">
                    <i class="fas fa-info-circle" style="color:var(--primary)"></i>
                    {{ $locale === 'en' ? 'Account details' : 'Détails du compte' }}
                </h4>
                <div style="display:flex;flex-direction:column;gap:.6rem;font-size:.88rem;color:var(--gray-500)">
                    <div style="display:flex;align-items:center;gap:.6rem">
                        <i class="fas fa-calendar" style="width:16px;color:var(--primary)"></i>
                        {{ $locale === 'en' ? 'Member since' : 'Membre depuis' }}
                        <strong style="color:var(--gray-700)">{{ $user->created_at->format('d/m/Y') }}</strong>
                    </div>
                    <div style="display:flex;align-items:center;gap:.6rem">
                        <i class="fas fa-globe" style="width:16px;color:var(--primary)"></i>
                        {{ $locale === 'en' ? 'Language' : 'Langue' }}
                        <strong style="color:var(--gray-700)">{{ strtoupper($user->locale ?? app()->getLocale()) }}</strong>
                    </div>
                    @if($user->is_admin)
                    <div style="display:flex;align-items:center;gap:.6rem">
                        <i class="fas fa-shield-alt" style="width:16px;color:var(--primary)"></i>
                        <span class="cw-statut-badge confirmee">Admin</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
    {{-- /onglet profil --}}

    {{-- ======================================================
         ONGLET 2 — MES RÉSERVATIONS
    ====================================================== --}}
    @elseif($tab === 'reservations')

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
        <h3 style="font-weight:700">
            <i class="fas fa-calendar-alt" style="color:var(--primary)"></i>
            {{ $locale === 'en' ? 'My recent bookings' : 'Mes réservations récentes' }}
        </h3>
        <a href="{{ route('espaces.index') }}" class="cw-btn cw-btn-primary cw-btn-sm">
            <i class="fas fa-plus"></i>
            {{ __('messages.nouvelle_reservation') }}
        </a>
    </div>

    @if($recentRez->isEmpty())
        <div class="cw-empty">
            <i class="fas fa-calendar-times"></i>
            <h3>{{ $locale === 'en' ? 'No bookings yet' : 'Aucune réservation' }}</h3>
            <p>{{ $locale === 'en' ? 'Book your first workspace now!' : 'Réservez votre premier espace de travail !' }}</p>
            <a href="{{ route('espaces.index') }}" class="cw-btn cw-btn-primary" style="margin-top:1rem">
                {{ __('messages.hero_cta') }}
            </a>
        </div>
    @else
        <div class="cw-reservation-list">
            @foreach($recentRez as $rez)
            <div class="cw-reservation-card {{ $rez->statut }}">
                <div>
                    <div class="cw-rez-espace">
                        <i class="fas fa-{{ $rez->espace->icone ?? 'building' }}" style="color:var(--primary)"></i>
                        {{ $rez->espace->nom_localised ?? $rez->espace->nom }}
                    </div>
                    <div class="cw-rez-dates">
                        <i class="fas fa-clock"></i>
                        {{ \Carbon\Carbon::parse($rez->debut)->format('d/m/Y H:i') }}
                        → {{ \Carbon\Carbon::parse($rez->fin)->format('d/m/Y H:i') }}
                    </div>
                    @if($rez->nb_personnes)
                    <div style="font-size:.82rem;color:var(--gray-400);margin-top:.2rem">
                        <i class="fas fa-users"></i>
                        {{ $rez->nb_personnes }}
                        {{ $locale === 'en' ? 'person(s)' : 'personne(s)' }}
                    </div>
                    @endif
                    <div style="margin-top:.4rem">
                        <span class="cw-statut-badge {{ $rez->statut }}">
                            @switch($rez->statut)
                                @case('confirmee') <i class="fas fa-check-circle"></i> @break
                                @case('en_attente') <i class="fas fa-clock"></i> @break
                                @case('terminee') <i class="fas fa-flag-checkered"></i> @break
                                @case('annulee') <i class="fas fa-times-circle"></i> @break
                                @case('prolongee') <i class="fas fa-forward"></i> @break
                            @endswitch
                            {{ __('messages.'.$rez->statut) }}
                        </span>
                    </div>
                </div>
                <div style="text-align:right">
                    <div class="cw-rez-prix">{{ number_format($rez->prix_total ?? 0, 2) }}€</div>
                    <div class="cw-rez-actions" style="margin-top:.5rem">
                        <a href="{{ route('espaces.show', $rez->espace) }}" class="cw-btn cw-btn-outline cw-btn-xs">
                            <i class="fas fa-eye"></i>
                            {{ $locale === 'en' ? 'View space' : 'Voir l\'espace' }}
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($totalRez > 5)
        <div style="text-align:center;margin-top:1.5rem">
            <a href="{{ route('reservations.index') }}" class="cw-btn cw-btn-outline">
                <i class="fas fa-list"></i>
                {{ $locale === 'en' ? 'View all bookings' : 'Voir toutes les réservations' }}
                <span class="cw-statut-badge en_attente" style="margin-left:.3rem">{{ $totalRez }}</span>
            </a>
        </div>
        @endif
    @endif

    {{-- ======================================================
         ONGLET 3 — MES FACTURES
    ====================================================== --}}
    @elseif($tab === 'factures')

    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
        <h3 style="font-weight:700">
            <i class="fas fa-file-invoice" style="color:var(--primary)"></i>
            {{ $locale === 'en' ? 'My recent invoices' : 'Mes factures récentes' }}
        </h3>
        <a href="{{ route('factures.index') }}" class="cw-btn cw-btn-outline cw-btn-sm">
            <i class="fas fa-folder-open"></i>
            {{ $locale === 'en' ? 'All invoices' : 'Toutes les factures' }}
        </a>
    </div>

    @if($recentFactures->isEmpty())
        <div class="cw-empty">
            <i class="fas fa-file-invoice"></i>
            <h3>{{ $locale === 'en' ? 'No invoices yet' : 'Aucune facture' }}</h3>
            <p>{{ $locale === 'en' ? 'Invoices will appear after your first booking.' : 'Les factures apparaissent après votre première réservation.' }}</p>
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:1rem">
            @foreach($recentFactures as $facture)
            <div class="cw-facture-card {{ $facture->statut }}">
                <div style="flex:1">
                    <div class="cw-facture-num">{{ $facture->numero }}</div>
                    <div class="cw-facture-meta">
                        @if($facture->reservation && $facture->reservation->espace)
                            {{ $facture->reservation->espace->nom }}
                            &nbsp;·&nbsp;
                        @endif
                        {{ $facture->date_emission->format('d/m/Y') }}
                    </div>
                    <span class="cw-statut-badge {{ $facture->statut === 'payee' ? 'confirmee' : ($facture->statut === 'emise' ? 'en_attente' : 'annulee') }}"
                          style="margin-top:.4rem">
                        {{ __('messages.facture_'.$facture->statut) }}
                    </span>
                </div>
                <div class="cw-facture-amount">{{ number_format($facture->montant_ttc, 2) }}€</div>
                <a href="{{ route('factures.pdf', $facture) }}" target="_blank"
                   class="cw-btn cw-btn-outline cw-btn-sm">
                    <i class="fas fa-file-pdf"></i>
                    {{ __('messages.telecharger_pdf') }}
                </a>
            </div>
            @endforeach
        </div>
    @endif

    @endif
    {{-- /onglets --}}

</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const alerts = document.querySelectorAll('.cw-alert[style*="position:static"]');
    alerts.forEach(el => {
        setTimeout(() => { el.style.opacity = '0'; el.style.transition = 'opacity .5s'; }, 4000);
        setTimeout(() => el.remove(), 4500);
    });
});
</script>
@endpush

@endsection
