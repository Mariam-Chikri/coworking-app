<div>
    {{-- Stats --}}
    <div class="cw-admin-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.5rem">
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-file-invoice"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['total'] }}</div><div class="cw-kpi-label">{{ __('messages.admin_factures_total') }}</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-check-circle"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['payees'] }}</div><div class="cw-kpi-label">{{ __('messages.admin_factures_payees') }}</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-hourglass"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['en_attente'] }}</div><div class="cw-kpi-label">{{ __('messages.admin_factures_en_attente') }}</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9)"><i class="fas fa-coins"></i></div>
            <div><div class="cw-kpi-value">{{ number_format($stats['revenus_total'], 0) }} MAD</div><div class="cw-kpi-label">{{ __('messages.admin_factures_revenus_totaux') }}</div></div>
        </div>
    </div>

    {{-- Filtres --}}
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('messages.admin_factures_rechercher') }}" class="cw-input" style="flex:1;min-width:200px">
        <select wire:model.live="filterStatut" class="cw-select" style="width:auto">
            <option value="">{{ __('messages.admin_factures_tous_statuts') }}</option>
            <option value="emise">{{ __('messages.admin_factures_emise') }}</option>
            <option value="payee">{{ __('messages.admin_factures_payee') }}</option>
            <option value="annulee">{{ __('messages.admin_factures_annulee') }}</option>
        </select>
        <input wire:model.live="dateDebut" type="date" class="cw-input" style="width:auto" placeholder="{{ __('messages.admin_reservations_date_debut') }}">
        <input wire:model.live="dateFin" type="date" class="cw-input" style="width:auto" placeholder="{{ __('messages.admin_reservations_date_fin') }}">
    </div>

    {{-- Tableau --}}
    <div class="cw-table-wrap">
        <table class="cw-table">
            <thead>
                <tr>
                    <th>{{ __('messages.admin_factures_numero') }}</th>
                    <th>{{ __('messages.admin_factures_client') }}</th>
                    <th>{{ __('messages.admin_factures_montant_ht') }}</th>
                    <th>{{ __('messages.admin_factures_tva') }}</th>
                    <th>{{ __('messages.admin_factures_montant_ttc') }}</th>
                    <th>{{ __('messages.admin_factures_statut') }}</th>
                    <th>{{ __('messages.admin_factures_emise_le') }}</th>
                    <th>{{ __('messages.admin_factures_payee_le') }}</th>
                    <th>{{ __('messages.admin_factures_actions') }}</th>
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
                        <div style="display:flex;gap:.4rem;flex-wrap:wrap">
                            {{-- Détails --}}
                            <button wire:click="voir({{ $f->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="{{ __('messages.admin_factures_details') }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            
                            {{-- Marquer payée / Remettre en émise --}}
                            @if($f->statut !== 'payee')
                                <button wire:click="marquerPayee({{ $f->id }})" class="cw-btn cw-btn-success cw-btn-xs" title="{{ __('messages.admin_factures_marquer_payee') }}">
                                    <i class="fas fa-check"></i>
                                </button>
                            @else
                                <button wire:click="marquerEmise({{ $f->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="{{ __('messages.admin_factures_remettre_emise') }}">
                                    <i class="fas fa-undo"></i>
                                </button>
                            @endif
                            
                            {{-- PDF --}}
                            @if($f->reservation_id)
                                <a href="{{ route('factures.pdf', $f->id) }}" class="cw-btn cw-btn-outline cw-btn-xs" title="{{ __('messages.admin_factures_telecharger_pdf') }}" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            @endif
                            
                            {{-- ✅ SUPPRIMER --}}
                            <button wire:click="confirmDelete({{ $f->id }})" 
                                    class="cw-btn cw-btn-danger cw-btn-xs" 
                                    title="{{ __('messages.supprimer') }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--gray-400)">{{ __('messages.admin_factures_aucune') }}</td></tr>
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
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">{{ __('messages.admin_factures_client') }}</span><strong>{{ $selected->user->name }}</strong></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">{{ __('messages.email') }}</span><span>{{ $selected->user->email }}</span></div>
                    @if($selected->reservation)
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">{{ __('messages.admin_factures_reservation') }}</span><span>{{ $selected->reservation->numero }}</span></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">{{ __('messages.admin_reservations_espace') }}</span><span>{{ $selected->reservation->espace->nom ?? '—' }}</span></div>
                    @endif
                    <hr style="border:none;border-top:1px solid var(--gray-100)">
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">{{ __('messages.admin_factures_montant_ht') }}</span><span>{{ number_format($selected->montant_ht, 2) }} MAD</span></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">{{ __('messages.admin_factures_tva') }} ({{ $selected->tva }}%)</span><span>{{ number_format($selected->montant_ttc - $selected->montant_ht, 2) }} MAD</span></div>
                    <div style="display:flex;justify-content:space-between;font-size:1.1rem"><span style="font-weight:700">{{ __('messages.admin_factures_montant_ttc') }}</span><strong>{{ number_format($selected->montant_ttc, 2) }} MAD</strong></div>
                    <hr style="border:none;border-top:1px solid var(--gray-100)">
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">{{ __('messages.admin_factures_statut') }}</span><span class="cw-statut-badge {{ $selected->statut === 'payee' ? 'confirmee' : 'en_attente' }}">{{ $selected->statut }}</span></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">{{ __('messages.admin_factures_emise_le') }}</span><span>{{ $selected->date_emission->format('d/m/Y') }}</span></div>
                    @if($selected->date_paiement)
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">{{ __('messages.admin_factures_payee_le') }}</span><span>{{ $selected->date_paiement->format('d/m/Y') }}</span></div>
                    <div style="display:flex;justify-content:space-between"><span style="color:var(--gray-500)">{{ __('messages.methode_paiement') }}</span><span>{{ $selected->methode_paiement }}</span></div>
                    @endif
                </div>
            </div>
            <div class="cw-modal-footer">
                @if($selected->statut !== 'payee')
                <button wire:click="marquerPayee({{ $selected->id }})" class="cw-btn cw-btn-success">
                    <i class="fas fa-check"></i> {{ __('messages.admin_factures_marquer_payee') }}
                </button>
                @endif
                @if($selected->reservation_id)
                <a href="{{ route('factures.pdf', $selected->id) }}" class="cw-btn cw-btn-outline" target="_blank">
                    <i class="fas fa-file-pdf"></i> {{ __('messages.admin_factures_telecharger_pdf') }}
                </a>
                @endif
                <button wire:click="$set('showModal', false)" class="cw-btn cw-btn-outline">{{ __('messages.admin_factures_fermer') }}</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Suppression --}}
    @if($showDeleteModal)
    <div class="cw-modal-overlay" wire:click.self="$set('showDeleteModal', false)">
        <div class="cw-modal" style="max-width:420px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                <h3 style="margin:0;color:var(--danger);font-size:1.1rem">
                    <i class="fas fa-exclamation-triangle"></i> 
                    {{ __('messages.admin_factures_supprimer_titre') ?? 'Supprimer la facture ?' }}
                </h3>
                <button wire:click="$set('showDeleteModal', false)" 
                        style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--gray-400)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="cw-modal-body">
                <p style="color:var(--gray-700)">
                    {{ __('messages.admin_factures_supprimer_irreversible') ?? 'Cette action est irréversible.' }}
                </p>
                <p style="font-size:.9rem;color:var(--gray-500);margin-top:.5rem">
                    {{ __('messages.admin_factures_supprimer_detail') ?? 'La facture et ses données seront définitivement supprimées.' }}
                </p>
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--gray-100)">
                <button wire:click="$set('showDeleteModal', false)" class="cw-btn cw-btn-outline">
                    {{ __('messages.annuler') ?? 'Annuler' }}
                </button>
                <button wire:click="delete" class="cw-btn cw-btn-danger">
                    <span wire:loading.remove wire:target="delete">
                        <i class="fas fa-trash"></i> {{ __('messages.supprimer') ?? 'Supprimer' }}
                    </span>
                    <span wire:loading wire:target="delete">
                        <i class="fas fa-spinner fa-spin"></i>
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>