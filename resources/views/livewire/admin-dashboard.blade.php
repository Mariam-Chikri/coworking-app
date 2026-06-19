<div>
    {{-- Header --}}
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:2rem;flex-wrap:wrap;gap:1rem">
        <h2 style="font-size:1.5rem;font-weight:700">
            <i class="fas fa-chart-line" style="color:var(--primary)"></i>
            {{ app()->getLocale() === 'en' ? 'Admin Dashboard' : 'Tableau de bord Admin' }}
        </h2>
        <div style="display:flex;align-items:center;gap:.75rem">
            <label style="font-size:.85rem;font-weight:600">Période :</label>
            <select wire:model.live="periodeStats" class="cw-select" style="width:auto">
                <option value="7">7 jours</option>
                <option value="30" selected>30 jours</option>
                <option value="90">90 jours</option>
            </select>
        </div>
    </div>

    {{-- KPIs --}}
    <div class="cw-admin-grid">
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-calendar-check"></i></div>
            <div>
                <div class="cw-kpi-value">{{ $kpis['reservations_actives'] ?? 0 }}</div>
                <div class="cw-kpi-label">{{ app()->getLocale() === 'en' ? 'Active bookings' : 'Réservations actives' }}</div>
            </div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-euro-sign"></i></div>
            <div>
                <div class="cw-kpi-value">{{ number_format($kpis['revenus_periode'] ?? 0, 0, ',', ' ') }}€</div>
                <div class="cw-kpi-label">{{ app()->getLocale() === 'en' ? 'Revenue' : 'Revenus' }} ({{ $periodeStats }}j)</div>
            </div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-users"></i></div>
            <div>
                <div class="cw-kpi-value">{{ $kpis['utilisateurs_total'] ?? 0 }}</div>
                <div class="cw-kpi-label">{{ app()->getLocale() === 'en' ? 'Total users' : 'Utilisateurs' }}</div>
            </div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-percentage"></i></div>
            <div>
                <div class="cw-kpi-value">{{ $kpis['taux_occupation_global'] ?? 0 }}%</div>
                <div class="cw-kpi-label">{{ app()->getLocale() === 'en' ? 'Occupancy rate' : 'Taux d\'occupation' }}</div>
            </div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-file-invoice"></i></div>
            <div>
                <div class="cw-kpi-value">{{ $kpis['reservations_periode'] ?? 0 }}</div>
                <div class="cw-kpi-label">{{ app()->getLocale() === 'en' ? 'Bookings' : 'Réservations' }} ({{ $periodeStats }}j)</div>
            </div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444)"><i class="fas fa-star"></i></div>
            <div>
                <div class="cw-kpi-value">{{ $kpis['avis_en_attente'] ?? 0 }}</div>
                <div class="cw-kpi-label">{{ app()->getLocale() === 'en' ? 'Reviews pending' : 'Avis en attente' }}</div>
            </div>
        </div>
    </div>

    {{-- Graphiques --}}
    <div class="cw-charts-grid">
        <div class="cw-chart-card">
            <h3><i class="fas fa-chart-bar" style="color:var(--primary)"></i>
                {{ app()->getLocale() === 'en' ? 'Monthly revenue (6 months)' : 'Revenus mensuels (6 mois)' }}
            </h3>
            <canvas id="chartRevenus" height="220"></canvas>
        </div>
        <div class="cw-chart-card">
            <h3><i class="fas fa-building" style="color:var(--primary)"></i>
                {{ app()->getLocale() === 'en' ? 'Most used spaces' : 'Espaces les plus utilisés' }}
            </h3>
            <canvas id="chartEspaces" height="220"></canvas>
        </div>
    </div>

    {{-- Espaces populaires --}}
    <div class="cw-chart-card" style="margin-bottom:1.5rem">
        <h3>{{ app()->getLocale() === 'en' ? 'Occupancy by space' : 'Occupation par espace' }}</h3>
        <div style="display:flex;flex-direction:column;gap:.75rem;margin-top:1rem">
            @foreach($espacesPopulaires as $e)
            <div>
                <div style="display:flex;justify-content:space-between;font-size:.88rem;font-weight:600;margin-bottom:.35rem">
                    <span>{{ $e['nom'] }}</span>
                    <span>{{ $e['taux_occupation'] }}% — {{ $e['nb_reservations'] }} rés.</span>
                </div>
                <div class="cw-occ-bar">
                    <div class="cw-occ-fill" style="width:{{ min($e['taux_occupation'], 100) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Avis en attente --}}
    @if(!empty($avisEnAttente))
    <div class="cw-chart-card" style="margin-bottom:1.5rem">
        <h3>⭐ {{ app()->getLocale() === 'en' ? 'Reviews awaiting validation' : 'Avis à valider' }}</h3>
        <div class="cw-avis-list" style="margin-top:1rem">
            @foreach($avisEnAttente as $avis)
            <div class="cw-avis-card" style="display:grid;grid-template-columns:1fr auto;gap:1rem;align-items:start">
                <div>
                    <div class="cw-avis-header">
                        <div class="cw-avatar" style="width:28px;height:28px;font-size:.75rem">{{ substr($avis['user']['name'],0,1) }}</div>
                        <strong style="font-size:.9rem">{{ $avis['user']['name'] }}</strong>
                        <span style="font-size:.85rem;color:var(--gray-500)">→ {{ $avis['espace']['nom'] }}</span>
                        <span class="cw-avis-stars">{{ str_repeat('★', $avis['note']) }}{{ str_repeat('☆', 5-$avis['note']) }}</span>
                    </div>
                    @if($avis['commentaire'])
                        <p class="cw-avis-texte">"{{ $avis['commentaire'] }}"</p>
                    @endif
                </div>
                <div style="display:flex;gap:.5rem">
                    <button wire:click="validerAvis({{ $avis['id'] }})" class="cw-btn cw-btn-success cw-btn-xs">
                        <i class="fas fa-check"></i>
                    </button>
                    <button wire:click="supprimerAvis({{ $avis['id'] }})" class="cw-btn cw-btn-danger cw-btn-xs">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Dernières réservations --}}
    <div class="cw-table-wrap">
        <div style="padding:1.25rem 1.5rem;border-bottom:1px solid var(--gray-100);font-weight:600">
            {{ app()->getLocale() === 'en' ? 'Recent bookings' : 'Dernières réservations' }}
        </div>
        <table class="cw-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>{{ app()->getLocale() === 'en' ? 'User' : 'Utilisateur' }}</th>
                    <th>{{ app()->getLocale() === 'en' ? 'Space' : 'Espace' }}</th>
                    <th>{{ app()->getLocale() === 'en' ? 'Start' : 'Début' }}</th>
                    <th>{{ app()->getLocale() === 'en' ? 'Status' : 'Statut' }}</th>
                    <th>{{ app()->getLocale() === 'en' ? 'Amount' : 'Montant' }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservationsRecentes as $rez)
                <tr>
                    <td style="font-size:.8rem;color:var(--gray-400)">{{ $rez->numero }}</td>
                    <td>{{ $rez->user->name }}</td>
                    <td>{{ $rez->espace->nom }}</td>
                    <td>{{ $rez->debut->format('d/m/Y H:i') }}</td>
                    <td><span class="cw-statut-badge {{ $rez->statut }}">{{ __('messages.'.$rez->statut) }}</span></td>
                    <td><strong>{{ number_format($rez->prix_total, 2) }} €</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('livewire:initialized', () => {
    initCharts();
});
document.addEventListener('livewire:update', () => {
    initCharts();
});

function initCharts() {
    const revData = @json($revenusMensuels);
    const espData = @json($espacesPopulaires);

    // Détruire si existants
    ['chartRevenus', 'chartEspaces'].forEach(id => {
        const c = Chart.getChart(id);
        if (c) c.destroy();
    });

    // Chart revenus
    const ctxR = document.getElementById('chartRevenus');
    if (ctxR && revData.length) {
        new Chart(ctxR, {
            type: 'bar',
            data: {
                labels: revData.map(d => d.mois),
                datasets: [{
                    label: 'Revenus (€)',
                    data: revData.map(d => d.revenus),
                    backgroundColor: 'rgba(102,126,234,.7)',
                    borderRadius: 6,
                }, {
                    label: 'Réservations',
                    data: revData.map(d => d.reservations),
                    type: 'line',
                    borderColor: '#764ba2',
                    backgroundColor: 'rgba(118,75,162,.1)',
                    yAxisID: 'y2',
                    tension: .4,
                    pointRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' }},
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f3f4f6' }},
                    y2: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }},
                }
            }
        });
    }

    // Chart espaces (doughnut)
    const ctxE = document.getElementById('chartEspaces');
    if (ctxE && espData.length) {
        new Chart(ctxE, {
            type: 'doughnut',
            data: {
                labels: espData.map(d => d.nom),
                datasets: [{
                    data: espData.map(d => d.nb_reservations),
                    backgroundColor: [
                        '#667eea','#764ba2','#10b981','#f59e0b','#ef4444',
                    ],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' },
                }
            }
        });
    }
}
</script>
@endpush
