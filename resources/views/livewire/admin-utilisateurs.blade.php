<div>
    {{-- Stats --}}
    <div class="cw-admin-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.5rem">
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-users"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['total'] }}</div><div class="cw-kpi-label">Total utilisateurs</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-shield-alt"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['admins'] }}</div><div class="cw-kpi-label">Administrateurs</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-user-plus"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['nouveaux'] }}</div><div class="cw-kpi-label">Nouveaux (30j)</div></div>
        </div>
    </div>

    {{-- Filtres --}}
   {{-- Filtres --}}
<div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center">
    <input 
        wire:model.live.debounce.300ms="search" 
        type="text" 
        placeholder="Nom, email, entreprise..." 
        class="cw-input" 
        style="flex:1;min-width:200px"
        id="search-input"
        autocomplete="off"
    >
    <select wire:model.live="filterRole" class="cw-select" style="width:auto" id="filter-role">
        <option value="">Tous les rôles</option>
        <option value="admin">Administrateurs</option>
        <option value="user">Utilisateurs</option>
    </select>
    <button wire:click="openCreate" class="cw-btn cw-btn-primary">
        <i class="fas fa-plus"></i> Ajouter
    </button>
</div>
    {{-- Tableau --}}
    <div class="cw-table-wrap">
        <table class="cw-table">
            <thead>
                <tr>
                    <th>id</th>
                    <th>Utilisateur</th>
                    <th>Entreprise</th>
                    <th>Téléphone</th>
                    <th>Réservations</th>
                    <th>Factures</th>
                    <th>Rôle</th>
                    <th>Inscrit le</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($utilisateurs as $u)
                <tr>
                    <td style="font-size:.8rem;color:var(--gray-400)">{{ $u->id }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:.75rem">
                                                      <div>
                                <strong>{{ $u->name }}</strong><br>
                                <small style="color:var(--gray-400)">{{ $u->email }}</small>
                            </div>
                        </div>
                    </td>
                    <td>{{ $u->entreprise ?? '—' }}</td>
                    <td>{{ $u->telephone ?? '—' }}</td>
                    <td>{{ $u->reservations_count }}</td>
                    <td>{{ $u->factures_count }}</td>
                    <td>
                        <span class="cw-statut-badge {{ $u->is_admin ? 'confirmee' : 'en_attente' }}">
                            {{ $u->is_admin ? 'Admin' : 'Utilisateur' }}
                        </span>
                    </td>
                    <td style="font-size:.85rem">{{ $u->created_at->format('d/m/Y') }}</td>
                    <td>
                        <div style="display:flex;gap:.4rem">
                            <button wire:click="openEdit({{ $u->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="Modifier">
                                <i class="fas fa-edit"></i>
                            </button>
                            @if($u->id !== auth()->id())
                              <button wire:click="toggleAdmin({{ $u->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="{{ $u->is_admin ? 'Révoquer admin' : 'Accorder admin' }}">
                                   <i class="fas fa-{{ $u->is_admin ? 'user-minus' : 'user-shield' }}"></i>
                              </button>
                            @else
                              <span style="font-size:0.7rem;color:var(--gray-400);padding:0.2rem 0.5rem;background:var(--gray-100);border-radius:4px;">
                                   <i class="fas fa-lock"></i> Protégé
                              </span>
                            @endif
                            @if($u->id !== auth()->id())
                            <button wire:click="confirmDelete({{ $u->id }})" class="cw-btn cw-btn-danger cw-btn-xs" title="Supprimer">
                                <i class="fas fa-trash"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--gray-400)">Aucun utilisateur trouvé</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="margin-top:1rem">{{ $utilisateurs->links() }}</div>

    {{-- Modal Créer / Modifier --}}
    {{-- Modal Créer / Modifier --}}
@if($showModal)
<div class="cw-modal-overlay" wire:click.self="$set('showModal', false)">
    <div class="cw-modal" style="max-width:500px">
        <div class="cw-modal-header">
            <h3>{{ $userId ? 'Modifier l\'utilisateur' : 'Nouvel utilisateur' }}</h3>
            <button wire:click="$set('showModal', false)" class="cw-modal-close"><i class="fas fa-times"></i></button>
        </div>
        <div class="cw-modal-body">
            <div style="display:grid;gap:1rem">
                <div class="cw-form-group">
                    <label class="cw-label">Nom complet *</label>
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
                    <label class="cw-label">Email *</label>
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
                        <small style="color:var(--gray-400);font-size:0.75rem;">L'email ne peut pas être modifié</small>
                    @endif
                </div>
                <div class="cw-form-group">
                    <label class="cw-label">Mot de passe {{ $userId ? '(laisser vide pour ne pas changer)' : '*' }}</label>
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
                    <label class="cw-label">Téléphone</label>
                    <input 
                        wire:model="form.telephone" 
                        type="text" 
                        class="cw-input" 
                        autocomplete="off"
                        id="edit-phone"
                    >
                </div>
                <div class="cw-form-group">
                    <label class="cw-label">Entreprise</label>
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
                        style="width:18px;height:18px"
                    >
                    <label for="is-admin-check" class="cw-label" style="margin:0">Accorder les droits administrateur</label>
                </div>
            </div>
        </div>
        <div class="cw-modal-footer">
            <button wire:click="$set('showModal', false)" class="cw-btn cw-btn-outline">Annuler</button>
            <button wire:click="save" class="cw-btn cw-btn-primary">
                <i class="fas fa-save"></i> {{ $userId ? 'Mettre à jour' : 'Créer' }}
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
                <h3 style="color:#ef4444"><i class="fas fa-exclamation-triangle"></i> Supprimer l'utilisateur ?</h3>
                <button wire:click="$set('showDeleteModal', false)" class="cw-modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="cw-modal-body"><p>Toutes les données liées à cet utilisateur (réservations, factures, avis) seront supprimées.</p></div>
            <div class="cw-modal-footer">
                <button wire:click="$set('showDeleteModal', false)" class="cw-btn cw-btn-outline">Annuler</button>
                <button wire:click="delete" class="cw-btn cw-btn-danger"><i class="fas fa-trash"></i> Supprimer</button>
            </div>
        </div>
    </div>
    @endif
</div>
