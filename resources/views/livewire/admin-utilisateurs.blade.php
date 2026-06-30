<div>
    {{-- Stats --}}
    <div class="cw-admin-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.5rem">
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-users"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['total'] }}</div><div class="cw-kpi-label">{{ __('messages.admin_utilisateurs_total') }}</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-shield-alt"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['admins'] }}</div><div class="cw-kpi-label">{{ __('messages.admin_utilisateurs_administrateurs') }}</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-user-plus"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['nouveaux'] }}</div><div class="cw-kpi-label">{{ __('messages.admin_utilisateurs_nouveaux') }}</div></div>
        </div>
    </div>

    {{-- Filtres --}}
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center">
        <input 
            wire:model.live.debounce.300ms="search" 
            type="text" 
            placeholder="{{ __('messages.admin_utilisateurs_rechercher') }}" 
            class="cw-input" 
            style="flex:1;min-width:200px"
            id="search-input"
            autocomplete="off"
        >
        <select wire:model.live="filterRole" class="cw-select" style="width:auto" id="filter-role">
            <option value="">{{ __('messages.admin_utilisateurs_tous_roles') }}</option>
            <option value="admin">{{ __('messages.admin_utilisateurs_administrateurs') }}</option>
            <option value="user">{{ __('messages.admin_utilisateur') }}</option>
        </select>
        <button wire:click="openCreate" class="cw-btn cw-btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.admin_utilisateurs_ajouter') }}
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
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_utilisateurs_id') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_utilisateurs_nom') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_utilisateurs_entreprise') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_utilisateurs_telephone') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:center">{{ __('messages.admin_utilisateurs_reservations') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:center">{{ __('messages.admin_utilisateurs_factures') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_utilisateurs_role') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:left">{{ __('messages.admin_utilisateurs_inscrit_le') }}</th>
                    <th style="padding:0.75rem 1rem;background:var(--gradient-soft);border-bottom:2px solid var(--gray-200);text-align:center;min-width:180px">{{ __('messages.admin_utilisateurs_actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($utilisateurs as $u)
                <tr style="border-bottom:1px solid var(--gray-100)">
                    <td style="padding:0.75rem 1rem;font-size:.8rem;color:var(--gray-400)">{{ $u->id }}</td>
                    <td style="padding:0.75rem 1rem">
                        <div style="display:flex;align-items:center;gap:.75rem">
                            <div>
                                <strong>{{ $u->name }}</strong><br>
                                <small style="color:var(--gray-400)">{{ $u->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td style="padding:0.75rem 1rem">{{ $u->entreprise ?? '—' }}</td>
                    <td style="padding:0.75rem 1rem">{{ $u->telephone ?? '—' }}</td>
                    <td style="padding:0.75rem 1rem;text-align:center">{{ $u->reservations_count }}</td>
                    <td style="padding:0.75rem 1rem;text-align:center">{{ $u->factures_count }}</td>
                    <td style="padding:0.75rem 1rem">
                        <span class="cw-statut-badge {{ $u->is_admin ? 'confirmee' : 'en_attente' }}">
                            {{ $u->is_admin ? __('messages.admin_utilisateurs_admin') : __('messages.admin_utilisateur') }}
                        </span>
                    </td>
                    <td style="padding:0.75rem 1rem;font-size:.85rem">{{ $u->created_at->format('d/m/Y') }}</td>
                    <td style="padding:0.75rem 1rem;text-align:center">
                        <div style="display:flex;gap:.4rem;justify-content:center">
                            <button wire:click="openEdit({{ $u->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="{{ __('messages.admin_utilisateurs_modifier') }}">
                                <i class="fas fa-edit"></i>
                            </button>
                            @if($u->id !== auth()->id())
                              <button wire:click="toggleAdmin({{ $u->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="{{ $u->is_admin ? __('messages.admin_utilisateurs_revoquer_admin') : __('messages.admin_utilisateurs_accorder_admin') }}">
                                   <i class="fas fa-{{ $u->is_admin ? 'user-minus' : 'user-shield' }}"></i>
                              </button>
                            @else
                              <span style="font-size:0.7rem;color:var(--gray-400);padding:0.2rem 0.5rem;background:var(--gray-100);border-radius:4px;display:inline-flex;align-items:center;gap:0.3rem">
                                   <i class="fas fa-lock"></i> {{ __('messages.admin_utilisateurs_protege') }}
                              </span>
                            @endif
                            @if($u->id !== auth()->id())
                            <button wire:click="confirmDelete({{ $u->id }})" class="cw-btn cw-btn-danger cw-btn-xs" title="{{ __('messages.admin_utilisateurs_supprimer') }}">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:3rem;color:var(--gray-400)">
                        <i class="fas fa-users" style="font-size:2rem;display:block;margin-bottom:.75rem;color:var(--gray-200)"></i>
                        {{ __('messages.admin_utilisateurs_aucun') }}
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Compteur --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem;padding:0.5rem 0;font-size:.85rem;color:var(--gray-500)">
        <span>
            <i class="fas fa-database" style="color:var(--primary);margin-right:0.5rem"></i>
            <strong>{{ $utilisateurs->count() }}</strong> 
            {{ app()->getLocale() === 'en' ? 'users displayed' : 'utilisateurs affichés' }}
        </span>
        <span style="display:flex;align-items:center;gap:0.5rem">
            <i class="fas fa-arrow-down" style="color:var(--primary)"></i>
            {{ app()->getLocale() === 'en' ? 'Scroll to view more' : 'Scrollez pour voir plus' }}
        </span>
    </div>

    {{-- Pagination --}}
    <div style="margin-top:1rem">{{ $utilisateurs->links() }}</div>

    {{-- Modal Créer / Modifier --}}
    @if($showModal)
    <div class="cw-modal-overlay" wire:click.self="$set('showModal', false)">
        <div class="cw-modal" style="max-width:500px">
            <div class="cw-modal-header">
                <h3>{{ $userId ? __('messages.admin_utilisateurs_modal_titre_modifier') : __('messages.admin_utilisateurs_modal_titre_creer') }}</h3>
                <button wire:click="$set('showModal', false)" class="cw-modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="cw-modal-body">
                <div style="display:grid;gap:1rem">
                    <div class="cw-form-group">
                        <label class="cw-label">{{ __('messages.admin_utilisateurs_nom_complet') }} *</label>
                        <input 
                            wire:model="form.name" 
                            type="text" 
                            class="cw-input" 
                            autocomplete="off"
                            id="edit-name"
                        >
                        @error('form.name')<span class="cw-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="cw-form-group">
                        <label class="cw-label">{{ __('messages.admin_utilisateurs_email') }} *</label>
                        <input 
                            wire:model="form.email" 
                            type="email" 
                            class="cw-input"
                            autocomplete="new-email"
                            id="edit-email"
                            {{ $userId ? 'readonly' : '' }}
                        >
                        @error('form.email')<span class="cw-error">{{ $message }}</span>@enderror
                        @if($userId)
                            <small style="color:var(--gray-400);font-size:0.75rem;">{{ __('messages.admin_utilisateurs_email_readonly') }}</small>
                        @endif
                    </div>
                    <div class="cw-form-group">
                        <label class="cw-label">{{ __('messages.admin_utilisateurs_mot_de_passe') }} {{ $userId ? __('messages.admin_utilisateurs_mot_de_passe_aide') : '*' }}</label>
                        <input 
                            wire:model="form.password" 
                            type="password" 
                            class="cw-input" 
                            placeholder="{{ $userId ? 'Nouveau mot de passe...' : '••••••••' }}"
                            autocomplete="new-password"
                            id="edit-password"
                        >
                        @error('form.password')<span class="cw-error">{{ $message }}</span>@enderror
                    </div>
                    <div class="cw-form-group">
                        <label class="cw-label">{{ __('messages.admin_utilisateurs_telephone') }}</label>
                        <input 
                            wire:model="form.telephone" 
                            type="text" 
                            class="cw-input" 
                            autocomplete="off"
                            id="edit-phone"
                        >
                    </div>
                    <div class="cw-form-group">
                        <label class="cw-label">{{ __('messages.admin_utilisateurs_entreprise') }}</label>
                        <input 
                            wire:model="form.entreprise" 
                            type="text" 
                            class="cw-input" 
                            autocomplete="off"
                            id="edit-company"
                        >
                    </div>
                    <div style="display:flex;align-items:center;gap:.5rem">
                        <input 
                            type="checkbox" 
                            wire:model="form.is_admin" 
                            id="is-admin-check" 
                            style="width:18px;height:18px">
                        <label for="is-admin-check" class="cw-label" style="margin:0">{{ __('messages.admin_utilisateurs_droits_admin') }}</label>
                    </div>
                </div>
            </div>
            <div class="cw-modal-footer">
                <button wire:click="$set('showModal', false)" class="cw-btn cw-btn-outline">{{ __('messages.annuler') }}</button>
                <button wire:click="save" class="cw-btn cw-btn-primary">
                    <i class="fas fa-save"></i> {{ $userId ? __('messages.admin_utilisateurs_mettre_a_jour') : __('messages.admin_utilisateurs_creer') }}
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Modal Supprimer --}}
    @if($showDeleteModal)
    <div class="cw-modal-overlay" wire:click.self="$set('showDeleteModal', false)">
        <div class="cw-modal" style="max-width:420px">
            <div class="cw-modal-header">
                <h3 style="color:#ef4444"><i class="fas fa-exclamation-triangle"></i> {{ __('messages.admin_utilisateurs_supprimer_titre') }}</h3>
                <button wire:click="$set('showDeleteModal', false)" class="cw-modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="cw-modal-body"><p>{{ __('messages.admin_utilisateurs_supprimer_detail') }}</p></div>
            <div class="cw-modal-footer">
                <button wire:click="$set('showDeleteModal', false)" class="cw-btn cw-btn-outline">{{ __('messages.annuler') }}</button>
                <button wire:click="delete" class="cw-btn cw-btn-danger"><i class="fas fa-trash"></i> {{ __('messages.supprimer') }}</button>
            </div>
        </div>
    </div>
    @endif
</div>