<div>
    {{-- KPI --}}
    <div class="cw-admin-grid" style="margin-bottom:2rem">
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-calendar-check"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['total_rez'] }}</div><div class="cw-kpi-label">Réservations totales</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-check-circle"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['actives'] }}</div><div class="cw-kpi-label">Réservations actives</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-coins"></i></div>
            <div><div class="cw-kpi-value">{{ number_format($stats['depenses'],0) }} MAD</div><div class="cw-kpi-label">Total dépensé</div></div>
        </div>
    </div>

    {{-- Onglets --}}
    <div style="display:flex;gap:.5rem;border-bottom:2px solid var(--gray-100);margin-bottom:1.75rem;flex-wrap:wrap">
        @foreach(['profil'=>['fas fa-user','Mon profil'],'securite'=>['fas fa-lock','Sécurité'],'danger'=>['fas fa-trash','Compte']] as $t => [$ico,$label])
        <button type="button"
                wire:click="$set('tab','{{ $t }}')"
                style="padding:.6rem 1.2rem;font-size:.9rem;font-weight:500;border:none;background:none;cursor:pointer;border-bottom:2px solid {{ $tab === $t ? 'var(--primary)' : 'transparent' }};color:{{ $tab === $t ? 'var(--primary)' : 'var(--gray-500)' }};margin-bottom:-2px;transition:.2s">
            <i class="{{ $ico }}"></i> {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- ══ Onglet : Profil ══ --}}
    @if($tab === 'profil')
    <div class="cw-card" style="padding:1.5rem;max-width:520px">
        <h3 style="font-size:1.05rem;font-weight:700;margin:0 0 1.25rem 0">Informations personnelles</h3>
        <div style="display:grid;gap:1rem">
            <div class="cw-form-group">
                <label class="cw-label">Nom complet *</label>
                <input wire:model="name" type="text" class="cw-input" autocomplete="name">
                @error('name')<span class="cw-form-error">{{ $message }}</span>@enderror
            </div>
            <div class="cw-form-group">
                <label class="cw-label">Adresse email *</label>
                <input wire:model="email" type="email" class="cw-input" autocomplete="email">
                @error('email')<span class="cw-form-error">{{ $message }}</span>@enderror
            </div>
            <div class="cw-form-group">
                <label class="cw-label">Téléphone</label>
                <input wire:model="telephone" type="tel" class="cw-input" placeholder="+212 6 00 00 00 00">
                @error('telephone')<span class="cw-form-error">{{ $message }}</span>@enderror
            </div>
            <div class="cw-form-group">
                <label class="cw-label">Entreprise</label>
                <input wire:model="entreprise" type="text" class="cw-input" placeholder="Nom de votre entreprise">
                @error('entreprise')<span class="cw-form-error">{{ $message }}</span>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem">
            <button type="button" wire:click="updateProfile" class="cw-btn cw-btn-primary">
                <span wire:loading.remove wire:target="updateProfile"><i class="fas fa-save"></i> Enregistrer</span>
                <span wire:loading wire:target="updateProfile"><i class="fas fa-spinner fa-spin"></i> Enregistrement…</span>
            </button>
        </div>
    </div>
    @endif

    {{-- ══ Onglet : Sécurité ══ --}}
    @if($tab === 'securite')
    <div class="cw-card" style="padding:1.5rem;max-width:520px">
        <h3 style="font-size:1.05rem;font-weight:700;margin:0 0 1.25rem 0">Changer le mot de passe</h3>
        <div style="display:grid;gap:1rem">
            <div class="cw-form-group">
                <label class="cw-label">Mot de passe actuel</label>
                <input wire:model="current_password" type="password" class="cw-input" autocomplete="current-password">
                @error('current_password')<span class="cw-form-error">{{ $message }}</span>@enderror
            </div>
            <div class="cw-form-group">
                <label class="cw-label">Nouveau mot de passe</label>
                <input wire:model="password" type="password" class="cw-input" autocomplete="new-password">
                @error('password')<span class="cw-form-error">{{ $message }}</span>@enderror
            </div>
            <div class="cw-form-group">
                <label class="cw-label">Confirmer le mot de passe</label>
                <input wire:model="password_confirmation" type="password" class="cw-input" autocomplete="new-password">
                @error('password_confirmation')<span class="cw-form-error">{{ $message }}</span>@enderror
            </div>
        </div>
        <div style="margin-top:1.25rem">
            <button type="button" wire:click="updatePassword" class="cw-btn cw-btn-primary">
                <span wire:loading.remove wire:target="updatePassword"><i class="fas fa-lock"></i> Mettre à jour</span>
                <span wire:loading wire:target="updatePassword"><i class="fas fa-spinner fa-spin"></i> Mise à jour…</span>
            </button>
        </div>
    </div>
    @endif

    {{-- ══ Onglet : Zone de danger ══ --}}
    @if($tab === 'danger')
    <div class="cw-card" style="padding:1.5rem;max-width:520px;border:1px solid #fca5a5">
        <h3 style="font-size:1.05rem;font-weight:700;margin:0 0 .5rem 0;color:var(--danger)">
            <i class="fas fa-exclamation-triangle"></i> Supprimer mon compte
        </h3>
        <p style="color:var(--gray-600);font-size:.9rem;margin:0 0 1rem 0">
            Cette action est <strong>irréversible</strong>. Toutes vos données (réservations, factures, avis) seront supprimées définitivement.
        </p>
        <button type="button" wire:click="$set('showDeleteModal',true)" class="cw-btn cw-btn-danger">
            <i class="fas fa-trash"></i> Supprimer mon compte
        </button>
    </div>

    @if($showDeleteModal)
    <div class="cw-modal-overlay" wire:click.self="$set('showDeleteModal',false)">
        <div class="cw-modal" style="max-width:420px">
            <h3 style="color:var(--danger);margin-bottom:.75rem"><i class="fas fa-exclamation-triangle"></i> Confirmer la suppression</h3>
            <p style="color:var(--gray-700);font-size:.9rem;margin-bottom:1rem">
                Entrez votre mot de passe pour confirmer la suppression définitive de votre compte.
            </p>
            <div class="cw-form-group">
                <input wire:model="delete_password" type="password" class="cw-input" placeholder="Votre mot de passe" autocomplete="current-password">
                @error('delete_password')<span class="cw-form-error">{{ $message }}</span>@enderror
            </div>
            <div style="display:flex;gap:.75rem;justify-content:flex-end;margin-top:1rem">
                <button type="button" wire:click="$set('showDeleteModal',false)" class="cw-btn cw-btn-outline">Annuler</button>
                <button type="button" wire:click="deleteAccount" class="cw-btn cw-btn-danger">
                    <span wire:loading.remove wire:target="deleteAccount"><i class="fas fa-trash"></i> Supprimer définitivement</span>
                    <span wire:loading wire:target="deleteAccount"><i class="fas fa-spinner fa-spin"></i> Suppression…</span>
                </button>
            </div>
        </div>
    </div>
    @endif
    @endif
</div>

