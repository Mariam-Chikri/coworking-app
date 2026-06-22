<div>
    {{-- Stats rapides --}}
    <div class="cw-admin-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.5rem">
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-list"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['total'] }}</div><div class="cw-kpi-label">Total</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-clock"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['en_attente'] }}</div><div class="cw-kpi-label">En attente</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-check"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['confirmees'] }}</div><div class="cw-kpi-label">Confirmées</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#ef4444,#b91c1c)"><i class="fas fa-times"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['annulees'] }}</div><div class="cw-kpi-label">Annulées</div></div>
        </div>
    </div>

    {{-- Filtres --}}
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Numéro, nom, email..." class="cw-input" style="flex:1;min-width:200px">
        <select wire:model.live="filterStatut" class="cw-select" style="width:auto">
            <option value="">Tous les statuts</option>
            <option value="en_attente">En attente</option>
            <option value="confirmee">Confirmée</option>
            <option value="prolongee">Prolongée</option>
            <option value="terminee">Terminée</option>
            <option value="annulee">Annulée</option>
        </select>
        <select wire:model.live="filterEspace" class="cw-select" style="width:auto">
            <option value="">Tous les espaces</option>
            @foreach($espaces as $e)
            <option value="{{ $e->id }}">{{ $e->nom }}</option>
            @endforeach
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
                    <th>Utilisateur</th>
                    <th>Espace</th>
                    <th>Début</th>
                    <th>Fin</th>
                    <th>Prix</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $r)
                <tr>
                    <td style="font-size:.8rem;font-weight:600">{{ $r->numero }}</td>
                    <td>
                        <strong>{{ $r->user->name }}</strong><br>
                        <small style="color:var(--gray-400)">{{ $r->user->email }}</small>
                    </td>
                    <td>{{ $r->espace->nom }}</td>
                    <td style="font-size:.85rem">{{ $r->debut->format('d/m/Y H:i') }}</td>
                    <td style="font-size:.85rem">{{ $r->fin->format('d/m/Y H:i') }}</td>
                    <td><strong>{{ number_format($r->prix_total, 2) }} MAD</strong></td>
                    <td><span class="cw-statut-badge {{ $r->statut }}">{{ $r->statut }}</span></td>
                    <td>
                        <div style="display:flex;gap:.4rem">
                            <button wire:click="voir({{ $r->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="Détails">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($r->statut === 'en_attente')
                            <button wire:click="confirmer({{ $r->id }})" class="cw-btn cw-btn-success cw-btn-xs" title="Confirmer">
                                <i class="fas fa-check"></i>
                            </button>
                            @endif
                            @if(in_array($r->statut, ['en_attente','confirmee','prolongee']))
                            <button wire:click="confirmerAnnulation({{ $r->id }})" class="cw-btn cw-btn-danger cw-btn-xs" title="Annuler">
                                <i class="fas fa-times"></i>
                            </button>
                            @endif
                            @if($r->statut === 'confirmee')
                            <button wire:click="terminer({{ $r->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="Marquer terminée">
                                <i class="fas fa-flag-checkered"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" style="text-align:center;padding:2rem;color:var(--gray-400)">Aucune réservation trouvée</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:1rem">{{ $reservations->links() }}</div>

    {{-- Modal Détails --}}
    @if($showModal && $selected)
    <div class="cw-modal-overlay" wire:click.self="$set('showModal', false)">
        <div class="cw-modal" style="max-width:540px">
            <div class="cw-modal-header">
                <h3><i class="fas fa-calendar"></i> {{ $selected->numero }}</h3>
                <button wire:click="$set('showModal', false)" class="cw-modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="cw-modal-body">
                <div style="display:grid;gap:.75rem">
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Utilisateur</span><strong>{{ $selected->user->name }}</strong></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Email</span><span>{{ $selected->user->email }}</span></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Espace</span><strong>{{ $selected->espace->nom }}</strong></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Début</span><span>{{ $selected->debut->format('d/m/Y H:i') }}</span></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Fin</span><span>{{ $selected->fin->format('d/m/Y H:i') }}</span></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Durée</span><span>{{ $selected->duree_heures }}h</span></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Montant total</span><strong>{{ number_format($selected->prix_total, 2) }} MAD</strong></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Statut</span><span class="cw-statut-badge {{ $selected->statut }}">{{ $selected->statut }}</span></div>
                    @if($selected->notes)
                    <div><span style="color:var(--gray-500)">Notes</span><p style="margin-top:.25rem">{{ $selected->notes }}</p></div>
                    @endif
                    @if($selected->facture)
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">Facture</span><span>{{ $selected->facture->numero }} — <span class="cw-statut-badge {{ $selected->facture->statut }}">{{ $selected->facture->statut }}</span></span></div>
                    @endif
                </div>
            </div>
            <div class="cw-modal-footer">
                @if($selected->statut === 'en_attente')
                <button wire:click="confirmer({{ $selected->id }})" class="cw-btn cw-btn-success"><i class="fas fa-check"></i> Confirmer</button>
                @endif
                <button wire:click="$set('showModal', false)" class="cw-btn cw-btn-outline">Fermer</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Annulation --}}
    @if($showCancelModal)
    <div class="cw-modal-overlay" wire:click.self="$set('showCancelModal', false)">
        <div class="cw-modal" style="max-width:420px">
            <div class="cw-modal-header">
                <h3 style="color:#ef4444"><i class="fas fa-exclamation-triangle"></i> Annuler la réservation ?</h3>
                <button wire:click="$set('showCancelModal', false)" class="cw-modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="cw-modal-body"><p>Cette action est irréversible. La réservation sera marquée comme annulée.</p></div>
            <div class="cw-modal-footer">
                <button wire:click="$set('showCancelModal', false)" class="cw-btn cw-btn-outline">Retour</button>
                <button wire:click="annuler" class="cw-btn cw-btn-danger"><i class="fas fa-times"></i> Annuler la réservation</button>
            </div>
        </div>
    </div>
    @endif
</div>
