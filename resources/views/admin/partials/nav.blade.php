{{-- Navigation admin partagée — wire:navigate sur chaque lien pour éviter les rechargements --}}
<div style="display:flex;gap:.75rem;flex-wrap:wrap;margin-bottom:2rem">
    @foreach([
        ['route'=>'admin.dashboard',      'icon'=>'chart-line',   'label'=>'Dashboard'],
        ['route'=>'admin.espaces',         'icon'=>'building',     'label'=>'Espaces'],
        ['route'=>'admin.reservations',    'icon'=>'calendar',     'label'=>'Réservations'],
        ['route'=>'admin.utilisateurs',    'icon'=>'users',        'label'=>'Utilisateurs'],
        ['route'=>'admin.avis',            'icon'=>'star',         'label'=>'Avis'],
        ['route'=>'admin.factures',        'icon'=>'file-invoice', 'label'=>'Factures'],
    ] as $nav)
    <a href="{{ route($nav['route']) }}"
       wire:navigate
       class="cw-btn {{ request()->routeIs($nav['route']) ? 'cw-btn-primary' : 'cw-btn-outline' }} cw-btn-sm">
        <i class="fas fa-{{ $nav['icon'] }}"></i> {{ $nav['label'] }}
    </a>
    @endforeach
</div>
