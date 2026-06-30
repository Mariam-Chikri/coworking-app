<div>
    {{-- Stats rapides --}}
    <div class="cw-admin-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.5rem">
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-list"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['total'] }}</div><div class="cw-kpi-label">{{ __('messages.admin_reservations_total') }}</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-clock"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['en_attente'] }}</div><div class="cw-kpi-label">{{ __('messages.admin_reservations_en_attente') }}</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-check"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['confirmees'] }}</div><div class="cw-kpi-label">{{ __('messages.admin_reservations_confirmees') }}</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#ef4444,#b91c1c)"><i class="fas fa-times"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['annulees'] }}</div><div class="cw-kpi-label">{{ __('messages.admin_reservations_annulees') }}</div></div>
        </div>
    </div>

    {{-- Filtres --}}
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center">
        <div style="position:relative;flex:1;min-width:200px">
            <i class="fas fa-search" style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);color:var(--gray-400);pointer-events:none"></i>
            <input wire:model.live.debounce.300ms="search" type="text" 
                   placeholder="{{ __('messages.admin_reservations_rechercher') }}" 
                   class="cw-input" style="padding-left:2.5rem" autocomplete="off">
        </div>
        <select wire:model.live="filterStatut" class="cw-select" style="width:auto;min-width:150px">
            <option value="">{{ __('messages.admin_reservations_tous_statuts') }}</option>
            <option value="en_attente">{{ __('messages.en_attente') }}</option>
            <option value="confirmee">{{ __('messages.confirmee') }}</option>
            <option value="prolongee">{{ __('messages.prolongee') }}</option>
            <option value="terminee">{{ __('messages.terminee') }}</option>
            <option value="annulee">{{ __('messages.annulee') }}</option>
        </select>
        <select wire:model.live="filterEspace" class="cw-select" style="width:auto;min-width:150px">
            <option value="">{{ __('messages.admin_reservations_tous_espaces') }}</option>
            @foreach($espaces as $e)
            <option value="{{ $e->id }}">{{ $e->nom }}</option>
            @endforeach
        </select>
        <input wire:model.live="dateDebut" type="date" class="cw-input" style="width:auto" placeholder="{{ __('messages.admin_reservations_date_debut') }}">
        <input wire:model.live="dateFin" type="date" class="cw-input" style="width:auto" placeholder="{{ __('messages.admin_reservations_date_fin') }}">
        <button wire:click="resetFilters" class="cw-btn cw-btn-outline">
            <i class="fas fa-undo"></i> {{ __('messages.reinitialiser') }}
        </button>
    </div>

    {{-- Tableau avec scroll --}}
    <div class="cw-table-wrap-scroll" style="max-height:500px;overflow-y:auto;border-radius:var(--radius-lg);box-shadow:var(--shadow-sm);border:1px solid var(--gray-100);position:relative">
        <div wire:loading.delay style="text-align:center;padding:1.5rem;position:sticky;top:0;background:white;z-index:10">
            <i class="fas fa-spinner fa-spin" style="color:var(--primary);font-size:1.5rem"></i>
        </div>
        
        <table class="cw-table" style="width:100%;border-collapse:collapse">
            <thead style="position:sticky;top:0;z-index:5;background:white">
                <tr>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_reservations_numero') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_reservations_utilisateur') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_reservations_espace') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_reservations_debut') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_reservations_fin') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_reservations_prix') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_reservations_statut') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:center;min-width:200px">{{ __('messages.admin_reservations_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reservations as $r)
                <tr style="border-bottom:1px solid var(--gray-100)">
                    <td style="padding:0.75rem 1rem;font-weight:600;font-size:.85rem">{{ $r->numero }}</td>
                    <td style="padding:0.75rem 1rem">
                        <strong>{{ $r->user->name }}</strong><br>
                        <small style="color:var(--gray-400)">{{ $r->user->email }}</small>
                    </td>
                    <td style="padding:0.75rem 1rem">{{ $r->espace->nom }}</td>
                    <td style="padding:0.75rem 1rem;font-size:.85rem">{{ $r->debut->format('d/m/Y H:i') }}</td>
                    <td style="padding:0.75rem 1rem;font-size:.85rem">{{ $r->fin->format('d/m/Y H:i') }}</td>
                    <td style="padding:0.75rem 1rem"><strong>{{ number_format($r->prix_total, 2) }} MAD</strong></td>
                    <td style="padding:0.75rem 1rem"><span class="cw-statut-badge {{ $r->statut }}">{{ $r->statut }}</span></td>
                    <td style="padding:0.75rem 1rem;text-align:center">
                        <div style="display:flex;gap:.35rem;flex-wrap:wrap;justify-content:center">
                            <button wire:click="voir({{ $r->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="{{ __('messages.admin_reservations_details') }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            @if($r->statut === 'en_attente')
                            <button wire:click="confirmer({{ $r->id }})" class="cw-btn cw-btn-success cw-btn-xs" title="{{ __('messages.admin_reservations_confirmer') }}">
                                <i class="fas fa-check"></i>
                            </button>
                            @endif
                            @if(in_array($r->statut, ['en_attente','confirmee','prolongee']))
                            <button wire:click="confirmerAnnulation({{ $r->id }})" class="cw-btn cw-btn-danger cw-btn-xs" title="{{ __('messages.admin_reservations_annuler') }}">
                                <i class="fas fa-times"></i>
                            </button>
                            @endif
                            @if($r->statut === 'confirmee')
                            <button wire:click="terminer({{ $r->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="{{ __('messages.admin_reservations_terminer') }}">
                                <i class="fas fa-flag-checkered"></i>
                            </button>
                            @endif
                            {{-- ✅ BOUTON SUPPRIMER --}}
                            <button wire:click="confirmDelete({{ $r->id }})" 
                                    class="cw-btn cw-btn-danger cw-btn-xs" 
                                    title="{{ __('messages.supprimer') }}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:3rem;color:var(--gray-400)">
                        <i class="fas fa-calendar-times" style="font-size:2rem;display:block;margin-bottom:.75rem;color:var(--gray-200)"></i>
                        {{ __('messages.admin_reservations_aucune') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Compteur et statut du chargement --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem;padding:0.5rem 0;font-size:.85rem;color:var(--gray-500)">
        <span>
            <i class="fas fa-database" style="color:var(--primary);margin-right:0.5rem"></i>
            <strong>{{ $reservations->count() }}</strong> 
            {{ app()->getLocale() === 'en' ? 'reservations displayed' : __('messages.admin_reservations_reservations_affichees') }}
        </span>
        <span style="display:flex;align-items:center;gap:0.5rem">
            <i class="fas fa-arrow-down" style="color:var(--primary)"></i>
            {{ app()->getLocale() === 'en' ? 'Scroll to view more' : __('messages.admin_reservations_scroll') }}
        </span>
    </div>

    {{-- Modal Détails --}}
    @if($showModal && $selected)
    <div class="cw-modal-overlay" wire:click.self="$set('showModal', false)">
        <div class="cw-modal" style="max-width:540px">
            <div class="cw-modal-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1.5rem">
                <h3 style="margin:0"><i class="fas fa-calendar" style="color:var(--primary)"></i> {{ $selected->numero }}</h3>
                <button wire:click="$set('showModal', false)" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--gray-400)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="cw-modal-body">
                <div style="display:grid;gap:.75rem">
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--gray-100)">
                        <span style="color:var(--gray-500)">{{ __('messages.admin_reservations_utilisateur') }}</span>
                        <strong>{{ $selected->user->name }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--gray-100)">
                        <span style="color:var(--gray-500)">{{ __('messages.email') }}</span>
                        <span>{{ $selected->user->email }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--gray-100)">
                        <span style="color:var(--gray-500)">{{ __('messages.admin_reservations_espace') }}</span>
                        <strong>{{ $selected->espace->nom }}</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--gray-100)">
                        <span style="color:var(--gray-500)">{{ __('messages.admin_reservations_debut') }}</span>
                        <span>{{ $selected->debut->format('d/m/Y H:i') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--gray-100)">
                        <span style="color:var(--gray-500)">{{ __('messages.admin_reservations_fin') }}</span>
                        <span>{{ $selected->fin->format('d/m/Y H:i') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--gray-100)">
                        <span style="color:var(--gray-500)">{{ __('messages.admin_reservations_duree') }}</span>
                        <span>{{ $selected->duree_heures }}h</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--gray-100)">
                        <span style="color:var(--gray-500)">{{ __('messages.admin_reservations_montant_total') }}</span>
                        <strong style="color:var(--primary)">{{ number_format($selected->prix_total, 2) }} MAD</strong>
                    </div>
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--gray-100)">
                        <span style="color:var(--gray-500)">{{ __('messages.admin_reservations_statut') }}</span>
                        <span class="cw-statut-badge {{ $selected->statut }}">{{ $selected->statut }}</span>
                    </div>
                    @if($selected->notes)
                    <div style="padding:.5rem 0">
                        <span style="color:var(--gray-500)">{{ __('messages.admin_reservations_notes') }}</span>
                        <p style="margin-top:.25rem;padding:.5rem;background:var(--gray-50);border-radius:8px">{{ $selected->notes }}</p>
                    </div>
                    @endif
                    @if($selected->facture)
                    <div style="display:flex;justify-content:space-between;padding:.5rem 0;border-bottom:1px solid var(--gray-100)">
                        <span style="color:var(--gray-500)">{{ __('messages.facture') }}</span>
                        <span>{{ $selected->facture->numero }} — <span class="cw-statut-badge {{ $selected->facture->statut }}">{{ $selected->facture->statut }}</span></span>
                    </div>
                    @endif
                </div>
            </div>
            <div class="cw-modal-footer" style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--gray-100)">
                @if($selected->statut === 'en_attente')
                <button wire:click="confirmer({{ $selected->id }})" class="cw-btn cw-btn-success">
                    <i class="fas fa-check"></i> {{ __('messages.admin_reservations_confirmer') }}
                </button>
                @endif
                <button wire:click="$set('showModal', false)" class="cw-btn cw-btn-outline">{{ __('messages.admin_reservations_fermer') }}</button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Annulation --}}
    @if($showCancelModal)
    <div class="cw-modal-overlay" wire:click.self="$set('showCancelModal', false)">
        <div class="cw-modal" style="max-width:420px">
            <div class="cw-modal-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                <h3 style="margin:0;color:#ef4444"><i class="fas fa-exclamation-triangle"></i> {{ __('messages.admin_reservations_annuler_titre') }}</h3>
                <button wire:click="$set('showCancelModal', false)" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--gray-400)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="cw-modal-body">
                <p>{{ __('messages.admin_reservations_annuler_irreversible') }}</p>
            </div>
            <div class="cw-modal-footer" style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--gray-100)">
                <button wire:click="$set('showCancelModal', false)" class="cw-btn cw-btn-outline">{{ __('messages.admin_reservations_retour') }}</button>
                <button wire:click="annuler" class="cw-btn cw-btn-danger">
                    <i class="fas fa-times"></i> {{ __('messages.admin_reservations_annuler_btn') }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ✅ Modal Suppression --}}
    @if($showDeleteModal)
    <div class="cw-modal-overlay" wire:click.self="$set('showDeleteModal', false)">
        <div class="cw-modal" style="max-width:420px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                <h3 style="margin:0;color:var(--danger);font-size:1.1rem">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Supprimer la réservation ?
                </h3>
                <button wire:click="$set('showDeleteModal', false)" 
                        style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:var(--gray-400)">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="cw-modal-body">
                <p style="color:var(--gray-700)">
                    Cette action est irréversible.
                </p>
                <p style="font-size:.9rem;color:var(--gray-500);margin-top:.5rem">
                    La réservation et toutes ses données associées seront définitivement supprimées.
                </p>
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1.5rem;padding-top:1.25rem;border-top:1px solid var(--gray-100)">
                <button wire:click="$set('showDeleteModal', false)" class="cw-btn cw-btn-outline">
                    Annuler
                </button>
                <button wire:click="delete" class="cw-btn cw-btn-danger">
                    <span wire:loading.remove wire:target="delete">
                        <i class="fas fa-trash"></i> Supprimer
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