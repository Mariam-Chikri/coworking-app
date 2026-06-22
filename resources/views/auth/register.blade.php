<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CoWork Space') — Connexion</title>
    <meta name="description" content="Espace de coworking premium — réservez votre espace de travail en ligne">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/coworking.css') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
    @stack('styles')
</head>
<body class="cw-body cw-auth-page">

<!-- Navbar simplifiée -->
<nav class="cw-navbar cw-navbar-auth">
    <div class="cw-container cw-nav-inner">
        <a href="{{ route('home') }}" class="cw-logo" wire:navigate>
            <img src="{{ asset('images/logo.png') }}" alt="CoWork Space" class="cw-logo-img" height="40" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
            <span style="display: flex; align-items: center; gap: 0.5rem;">
                <i class="fas fa-building"></i>
                <span>CoWork<strong>Space</strong></span>
            </span>
        </a>

        <ul class="cw-nav-links">
            <li><a href="{{ route('home') }}" wire:navigate>{{ __('messages.accueil') ?? 'Accueil' }}</a></li>
            <li><a href="{{ route('espaces.index') }}" wire:navigate>{{ __('messages.espaces') ?? 'Espaces' }}</a></li>
        </ul>

        <div class="cw-nav-actions">
            <div class="cw-lang-switcher">
                <a href="{{ route('lang.switch', 'fr') }}" class="{{ app()->getLocale() === 'fr' ? 'active' : '' }}" wire:navigate>FR</a>
                <span>|</span>
                <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}" wire:navigate>EN</a>
            </div>
        </div>
    </div>
</nav>

<!-- Main content -->
<main class="cw-main cw-auth-main">
    <div class="cw-auth-container">
        <div class="cw-auth-card">
            <!-- Header -->
            <div class="cw-auth-header">
                <a href="{{ route('home') }}" class="cw-auth-logo" wire:navigate>
                    <img src="{{ asset('images/logo.png') }}" alt="CoWork Space" class="cw-auth-logo-img" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <span style="display: none;" class="cw-auth-logo-icon">
                        <i class="fas fa-building"></i>
                    </span>
                </a>
                <h1 class="cw-auth-title">
                    @yield('auth_title', 'Bienvenue')
                </h1>
                <p class="cw-auth-subtitle">
                    @yield('auth_subtitle', 'Connectez-vous à votre compte')
                </p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="cw-auth-status success mb-4">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Formulaire -->
            <div class="cw-auth-body">
                {{ $slot }}
            </div>

            <!-- Footer links -->
            <div class="cw-auth-footer">
                @yield('auth_footer')
            </div>
        </div>
    </div>
</main>

<!-- Footer simplifié -->
<footer class="cw-footer cw-footer-auth">
    <div class="cw-container cw-footer-inner">
        <div class="cw-footer-brand">
            <a href="{{ route('home') }}" class="cw-logo" wire:navigate>
                <i class="fas fa-building"></i>
                <span>CoWork<strong>Space</strong></span>
            </a>
            <p>{{ __('messages.footer_desc') ?? 'Espace de coworking premium' }}</p>
        </div>
        <div class="cw-footer-links">
            <h4>{{ __('messages.liens_utiles') ?? 'Liens utiles' }}</h4>
            <a href="{{ route('espaces.index') }}" wire:navigate>{{ __('messages.espaces') ?? 'Espaces' }}</a>
            <a href="{{ route('home') }}#a-propos" wire:navigate>{{ __('messages.a_propos') ?? 'À propos' }}</a>
            <a href="{{ route('home') }}#contact" wire:navigate>Contact</a>
        </div>
    </div>
    <div class="cw-footer-bottom">
        <p>&copy; {{ date('Y') }} CoWorkSpace. {{ __('messages.droits_reserves') ?? 'Tous droits réservés' }}</p>
    </div>
</footer>

@livewireScripts

@stack('scripts')
</body>
</html>