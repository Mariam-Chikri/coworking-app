<div>
    {{-- Stats --}}
    <div class="cw-admin-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.5rem">
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-file-invoice"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['total'] }}</div><div class="cw-kpi-label">Total factures</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-check-circle"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['payees'] }}</div><div class="cw-kpi-label">Payées</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-hourglass"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['en_attente'] }}</div><div class="cw-kpi-label">En attente</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9)"><i class="fas fa-coins"></i></div>
            <div><div class="cw-kpi-value">{{ number_format($stats['revenus_total'], 0) }} MAD</div><div class="cw-kpi-label">Revenus totaux</div></div>
        </div>
    </div>

    {{-- Filtres --}}
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Numéro, nom, email..." class="cw-input" style="flex:1;min-width:200px">
        <select wire:model.live="filterStatut" class="cw-select" style="width:auto">
            <option value="">Tous les statuts</option>
            <option value="emise">Émise</option>
            <option value="payee">Payée</option>
            <option value="annulee">Annulée</option>
        </select>
        <input wire:model.live="dateDebut" type="date" class="cw-input" style="width:auto">
        <input wire:model.live="dateFin" type="date" class="cw-input" style="width:auto">
    </div>

    {{-- Tableau --}}
    <div class="cw-table-wrap">
        <table class="cw-table">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Client</th>
                    <th>Montant HT</th>
                    <th>TVA</th>
                    <th>Montant TTC</th>
                    <th>Statut</th>
                    <th>Émise le</th>
                    <th>Payée le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($factures as $f)
                <tr>
                    <td style="font-weight:600;font-size:.85rem">{{ $f->numero }}</td>
                    <td>
                        <strong>{{ $f->user->name }}</strong><br>
                        <small style="color:var(--gray-400)">{{ $f->user->email }}</small>
                    </td>
                    <td>{{ number_format($f->montant_ht, 2) }} MAD</td>
                    <td>{{ $f->tva }}%</td>
                    <td><strong>{{ number_format($f->montant_ttc, 2) }} MAD</strong></td>
                    <td><span class="cw-statut-badge {{ $f->statut === 'payee' ? 'confirmee' : ($f->statut === 'annulee' ? 'annulee' : 'en_attente') }}">{{ $f->statut }}</span></td>
                    <td style="font-size:.85rem">{{ $f->date_emission->format('d/m/Y') }}</td>
                    <td style="font-size:.85rem">{{ $f->date_paiement ? $f->date_paiement->format('d/m/Y') : '—' }}</td>
                    <td>
                        <div style="display:flex;gap:.4rem">
                            <button wire:click="voir({{ $f->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="Détails">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($f->statut !== 'payee')
                            <button wire:click="marquerPayee({{ $f->id }})" class="cw-btn cw-btn-success cw-btn-xs" title="Marquer payée">
                                <i class="fas fa-check"></i>
                            </button>
                            @else
                            <button wire:click="marquerEmise({{ $f->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="Remettre en émise">
                                <i class="fas fa-undo"></i>
                            </button>
                            @endif
                            @if($f->reservation_id)
                            <a href="{{ route('factures.pdf', $f->id) }}" class="cw-btn cw-btn-outline cw-btn-xs" title="Télécharger PDF" target="_blank">
                                <i class="fas fa-file-pdf"></i>
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--gray-400)">Aucune facture trouvée</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:1rem">{{ $factures->links() }}</div>

    {{-- Modal Détails --}}
    @if($showModal && $selected)
    <div class="cw-modal-overlay" wire:click.self="$set('showModal', false)">
        <div class="cw-modal" style="max-width:500px">
            <div class="cw-modal-header">
                <h3><i class="fas fa-file-invoice"></i> {{ $selected->numero }}</h3>
                <button wire:click="$set('showModal', false)" class="cw-modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="cw-modal-body">
                <div style="display:grid;gap:.75rem">
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Client</span><strong>{{ $selected->user->name }}</strong></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Email</span><span>{{ $selected->user->email }}</span></div>
                    @if($selected->reservation)
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Réservation</span><span>{{ $selected->reservation->numero }}</span></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Espace</span><span>{{ $selected->reservation->espace->nom ?? '—' }}</span></div>
                    @endif
                    <hr style="border:none;border-top:1px solid var(--gray-100)">
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Montant HT</span><span>{{ number_format($selected->montant_ht, 2) }} MAD</span></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">TVA ({{ $selected->tva }}%)</span><span>{{ number_format($selected->montant_ttc - $selected->montant_ht, 2) }} MAD</span></div>
                    <div style="display:flex;justify-content:space-between;font-size:1.1rem"><span style="font-weight:700">Total TTC</span><strong>{{ number_format($selected->montant_ttc, 2) }} MAD</strong></div>
                    <hr style="border:none;border-top:1px solid var(--gray-100)">
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Statut</span><span class="cw-statut-badge {{ $selected->statut === 'payee' ? 'confirmee' : 'en_attente' }}">{{ $selected->statut }}</span></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Émise le</span><span>{{ $selected->date_emission->format('d/m/Y') }}</span></div>
                    @if($selected->date_paiement)
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Payée le</span><span>{{ $selected->date_paiement->format('d/m/Y') }}</span></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Méthode</span><span>{{ $selected->methode_paiement }}</span></div>
                    @endif
                </div>
            </div>
            <div class="cw-modal-footer">
                @if($selected->statut !== 'payee')
                <button wire:click="marquerPayee({{ $selected->id }})" class="cw-btn cw-btn-success">
                    <i class="fas fa-check"></i> Marquer payée
                </button>
                @endif
                @if($selected->reservation_id)
                <a href="{{ route('factures.pdf', $selected->id) }}" class="cw-btn cw-btn-outline" target="_blank">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
                @endif
                <button wire:click="$set('showModal', false)" class="cw-btn cw-btn-outline">Fermer</button>
            </div>
        </div>
    </div>
    @endif
</div>
