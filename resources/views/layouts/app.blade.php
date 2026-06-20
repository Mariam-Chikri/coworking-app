<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'CoWork Space') — CoWork</title>
    <meta name="description" content="@yield('description', 'Espace de coworking premium — réservez votre espace de travail en ligne')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/coworking.css') }}">

    @livewireStyles
    @stack('styles')
</head>
<body class="cw-body">

<!-- Navbar -->
<nav class="cw-navbar">
    <div class="cw-container cw-nav-inner">
        <a href="{{ route('home') }}" class="cw-logo" wire:navigate>
            <i class="fas fa-building"></i>
            <span>CoWork<strong>Space</strong></span>
        </a>

        <ul class="cw-nav-links">
            <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}" wire:navigate>{{ __('messages.accueil') }}</a></li>
            <li><a href="{{ route('espaces.index') }}" class="{{ request()->routeIs('espaces.*') ? 'active' : '' }}" wire:navigate>{{ __('messages.espaces') }}</a></li>
            @auth
                <li><a href="{{ route('reservations.index') }}" class="{{ request()->routeIs('reservations.*') ? 'active' : '' }}" wire:navigate>{{ __('messages.mes_reservations') }}</a></li>
                <li><a href="{{ route('favoris') }}" class="{{ request()->routeIs('favoris') ? 'active' : '' }}" wire:navigate><i class="fas fa-heart"></i></a></li>
                @if(auth()->user()->is_admin)
                    <li><a href="{{ route('admin.dashboard') }}" class="cw-badge-admin" wire:navigate>Admin</a></li>
                @endif
            @endauth
        </ul>

        <div class="cw-nav-actions">
            <!-- Langue switcher -->
            <div class="cw-lang-switcher">
                <a href="{{ route('lang.switch', 'fr') }}" class="{{ app()->getLocale() === 'fr' ? 'active' : '' }}" wire:navigate>FR</a>
                <span>|</span>
                <a href="{{ route('lang.switch', 'en') }}" class="{{ app()->getLocale() === 'en' ? 'active' : '' }}" wire:navigate>EN</a>
            </div>

            @guest
                <a href="{{ route('login') }}" class="cw-btn cw-btn-outline" wire:navigate>{{ __('messages.connexion') }}</a>
                <a href="{{ route('register') }}" class="cw-btn cw-btn-primary" wire:navigate>{{ __('messages.inscription') }}</a>
            @else
                <div class="cw-user-menu">
                    <button class="cw-user-btn" id="userMenuButton" type="button">
                        <div class="cw-avatar">{{ substr(auth()->user()->name, 0, 1) }}</div>
                        <span>{{ auth()->user()->name }}</span>
                        <i class="fas fa-chevron-down" id="userMenuChevron"></i>
                    </button>
                    <div class="cw-dropdown" id="userDropdown">
                        <a href="{{ route('profile') }}" wire:navigate><i class="fas fa-user"></i> {{ __('messages.mon_profil') }}</a>
                        <a href="{{ route('reservations.index') }}" wire:navigate><i class="fas fa-calendar"></i> {{ __('messages.mes_reservations') }}</a>
                        <a href="{{ route('factures.index') }}" wire:navigate><i class="fas fa-file-invoice"></i> {{ __('messages.mes_factures') }}</a>
                        <a href="{{ route('favoris') }}" wire:navigate><i class="fas fa-heart"></i> {{ __('messages.favoris') }}</a>
                        <hr>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"><i class="fas fa-sign-out-alt"></i> {{ __('messages.deconnexion') }}</button>
                        </form>
                    </div>
                </div>
            @endguest
        </div>

        <button class="cw-hamburger" id="hamburger">
            <span></span><span></span><span></span>
        </button>
    </div>
</nav>

<!-- Mobile nav -->
<div class="cw-mobile-nav" id="mobile-nav">
    <a href="{{ route('home') }}" wire:navigate>{{ __('messages.accueil') }}</a>
    <a href="{{ route('espaces.index') }}" wire:navigate>{{ __('messages.espaces') }}</a>
    @auth
        <a href="{{ route('reservations.index') }}" wire:navigate>{{ __('messages.mes_reservations') }}</a>
        <a href="{{ route('favoris') }}" wire:navigate>{{ __('messages.favoris') }}</a>
    @endauth
    <div class="cw-lang-switcher">
        <a href="{{ route('lang.switch', 'fr') }}" wire:navigate>FR</a> | <a href="{{ route('lang.switch', 'en') }}" wire:navigate>EN</a>
    </div>
</div>

<!-- Flash messages -->
@if(session('success'))
    <div class="cw-alert cw-alert-success" id="flashSuccess">
        <i class="fas fa-check-circle"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="cw-alert cw-alert-error" id="flashError">
        <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
    </div>
@endif

<!-- Toast notifications (Livewire) -->
<div class="cw-toast-container" id="toast-container"></div>

<!-- Main content -->
<main class="cw-main">
    @yield('content')
</main>

<!-- Footer -->
<footer class="cw-footer">
    <div class="cw-container cw-footer-inner">
        <div class="cw-footer-brand">
            <a href="{{ route('home') }}" class="cw-logo" wire:navigate>
                <i class="fas fa-building"></i>
                <span>CoWork<strong>Space</strong></span>
            </a>
            <p>{{ __('messages.footer_desc') }}</p>
        </div>
        <div class="cw-footer-links">
            <h4>{{ __('messages.liens_utiles') }}</h4>
            <a href="{{ route('espaces.index') }}" wire:navigate>{{ __('messages.espaces') }}</a>
            <a href="{{ route('home') }}#a-propos" wire:navigate>{{ __('messages.a_propos') }}</a>
            <a href="{{ route('home') }}#contact" wire:navigate>Contact</a>
        </div>
        <div class="cw-footer-contact">
            <h4>Contact</h4>
            <p><i class="fas fa-map-marker-alt"></i> 42 Rue du Coworking, Paris</p>
            <p><i class="fas fa-phone"></i> +33 1 23 45 67 89</p>
            <p><i class="fas fa-envelope"></i> contact@coworking.fr</p>
        </div>
    </div>
    <div class="cw-footer-bottom">
        <p>&copy; {{ date('Y') }} CoWorkSpace. {{ __('messages.droits_reserves') }}</p>
    </div>
</footer>

<!-- Chatbot -->
@livewire('chatbot-faq')

@livewireScripts

<script>
    // ============================================
    // GESTION DU DROPDOWN UTILISATEUR (PUR JS)
    // ============================================
    (function() {
        'use strict';

        let dropdownOpen = false;
        const button = document.getElementById('userMenuButton');
        const dropdown = document.getElementById('userDropdown');
        const chevron = document.getElementById('userMenuChevron');

        function toggleDropdown(e) {
            e.stopPropagation();
            dropdownOpen = !dropdownOpen;
            
            if (dropdownOpen) {
                dropdown.classList.add('show');
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            } else {
                dropdown.classList.remove('show');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        }

        function closeDropdown() {
            dropdownOpen = false;
            dropdown.classList.remove('show');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }

        // Initialisation
        function initDropdown() {
            if (button && dropdown) {
                // Supprimer les anciens listeners pour éviter les doublons
                button.removeEventListener('click', toggleDropdown);
                // Ajouter le nouveau listener
                button.addEventListener('click', toggleDropdown);
                
                // Fermer en cliquant ailleurs
                document.removeEventListener('click', closeDropdown);
                document.addEventListener('click', closeDropdown);
                
                // Empêcher la fermeture quand on clique dans le dropdown
                dropdown.removeEventListener('click', function(e) { e.stopPropagation(); });
                dropdown.addEventListener('click', function(e) { e.stopPropagation(); });
            }
        }

        // Attendre que le DOM soit chargé
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initDropdown);
        } else {
            initDropdown();
        }

        // Réinitialiser après navigation Livewire
        document.addEventListener('livewire:navigated', function() {
            // Réinitialiser l'état du dropdown
            if (dropdown) {
                dropdown.classList.remove('show');
            }
            if (chevron) {
                chevron.style.transform = 'rotate(0deg)';
            }
            dropdownOpen = false;
            // Réinitialiser les listeners
            setTimeout(initDropdown, 100);
        });

    })();

    // ============================================
    // HAMBURGER MENU
    // ============================================
    (function() {
        const hamburger = document.getElementById('hamburger');
        const mobileNav = document.getElementById('mobile-nav');

        function toggleMobileNav() {
            if (mobileNav) {
                mobileNav.classList.toggle('open');
            }
        }

        function initHamburger() {
            if (hamburger && mobileNav) {
                hamburger.removeEventListener('click', toggleMobileNav);
                hamburger.addEventListener('click', toggleMobileNav);
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initHamburger);
        } else {
            initHamburger();
        }

        document.addEventListener('livewire:navigated', function() {
            setTimeout(initHamburger, 100);
        });
    })();

    // ============================================
    // AUTO-HIDE ALERTS
    // ============================================
    (function() {
        function hideAlert(element) {
            if (element) {
                setTimeout(() => {
                    element.style.opacity = '0';
                    element.style.transition = 'opacity 0.5s ease';
                }, 4000);
                setTimeout(() => {
                    if (element.parentNode) {
                        element.remove();
                    }
                }, 4500);
            }
        }

        const successAlert = document.getElementById('flashSuccess');
        const errorAlert = document.getElementById('flashError');
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                hideAlert(successAlert);
                hideAlert(errorAlert);
            });
        } else {
            hideAlert(successAlert);
            hideAlert(errorAlert);
        }

        document.addEventListener('livewire:navigated', function() {
            hideAlert(document.getElementById('flashSuccess'));
            hideAlert(document.getElementById('flashError'));
        });
    })();
    
    

    // ============================================
    // LIVEWIRE TOAST LISTENER
    // ============================================
    document.addEventListener('livewire:init', () => {
        Livewire.on('toast', ({ message, type = 'info' }) => {
            const toast = document.createElement('div');
            toast.className = `cw-toast cw-toast-${type}`;
            toast.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i> ${message}`;
            document.getElementById('toast-container').appendChild(toast);
            setTimeout(() => toast.classList.add('show'), 10);
            setTimeout(() => { 
                toast.classList.remove('show'); 
                setTimeout(() => toast.remove(), 300); 
            }, 4000);
        });
    });
</script>

@stack('scripts')
</body>
</html>
