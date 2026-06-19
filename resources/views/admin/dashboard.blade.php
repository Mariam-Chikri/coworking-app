@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
<div class="cw-page-header">
    <div class="cw-container">
        <h1><i class="fas fa-shield-alt"></i> Admin</h1>
        <p>{{ app()->getLocale() === 'en' ? 'Full control over the platform' : 'Contrôle complet de la plateforme' }}</p>
    </div>
</div>
<div class="cw-container" style="padding-bottom:4rem">
    {{-- Navigation admin --}}
    <div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:2rem">
        @foreach([
            ['route'=>'admin.dashboard','icon'=>'chart-line','label'=>'Dashboard'],
            ['route'=>'admin.espaces','icon'=>'building','label'=>'Espaces'],
            ['route'=>'admin.reservations','icon'=>'calendar','label'=>'Réservations'],
            ['route'=>'admin.utilisateurs','icon'=>'users','label'=>'Utilisateurs'],
            ['route'=>'admin.avis','icon'=>'star','label'=>'Avis'],
            ['route'=>'admin.factures','icon'=>'file-invoice','label'=>'Factures'],
        ] as $nav)
        <a href="{{ route($nav['route']) }}"
           class="cw-btn {{ request()->routeIs($nav['route']) ? 'cw-btn-primary' : 'cw-btn-outline' }} cw-btn-sm">
            <i class="fas fa-{{ $nav['icon'] }}"></i> {{ $nav['label'] }}
        </a>
        @endforeach
    </div>

    @livewire('admin-dashboard')
</div>
@endsection
