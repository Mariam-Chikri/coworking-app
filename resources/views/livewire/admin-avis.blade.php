<div>
    {{-- Stats --}}
    <div class="cw-admin-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:1.5rem">
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon"><i class="fas fa-comments"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['total'] }}</div><div class="cw-kpi-label">{{ __('messages.admin_avis_total') }}</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706)"><i class="fas fa-clock"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['en_attente'] }}</div><div class="cw-kpi-label">{{ __('messages.admin_avis_en_attente') }}</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#10b981,#059669)"><i class="fas fa-check"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['valides'] }}</div><div class="cw-kpi-label">{{ __('messages.admin_avis_valides') }}</div></div>
        </div>
        <div class="cw-kpi-card">
            <div class="cw-kpi-icon" style="background:linear-gradient(135deg,#8b5cf6,#6d28d9)"><i class="fas fa-star"></i></div>
            <div><div class="cw-kpi-value">{{ $stats['moyenne'] }}/5</div><div class="cw-kpi-label">{{ __('messages.admin_avis_note_moyenne') }}</div></div>
        </div>
    </div>

    {{-- Filtres --}}
    <div style="display:flex;gap:1rem;flex-wrap:wrap;margin-bottom:1.5rem;align-items:center">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('messages.admin_avis_rechercher') }}" class="cw-input" style="flex:1;min-width:200px">
        <select wire:model.live="filterStatut" class="cw-select" style="width:auto">
            <option value="">{{ __('messages.admin_avis_tous') }}</option>
            <option value="en_attente">{{ __('messages.admin_avis_en_attente') }}</option>
            <option value="valide">{{ __('messages.admin_avis_valides') }}</option>
        </select>
        <select wire:model.live="filterEspace" class="cw-select" style="width:auto">
            <option value="">{{ __('messages.admin_reservations_tous_espaces') }}</option>
            @foreach($espaces as $e)
            <option value="{{ $e->id }}">{{ $e->nom }}</option>
            @endforeach
        </select>
        <select wire:model.live="filterNote" class="cw-select" style="width:auto">
            <option value="">{{ __('messages.admin_avis_toutes_notes') }}</option>
            @for($i = 5; $i >= 1; $i--)
            <option value="{{ $i }}">{{ $i }} ★</option>
            @endfor
        </select>
    </div>

    {{-- Liste avis --}}
    <div style="display:flex;flex-direction:column;gap:1rem">
        @forelse($avis as $a)
        <div class="cw-chart-card" style="padding:1.25rem">
            <div style="display:grid;grid-template-columns:1fr auto;gap:1rem;align-items:start">
                <div>
                    <div style="display:flex;align-items:center;gap:.75rem;margin-bottom:.5rem;flex-wrap:wrap">
                        <div class="cw-avatar" style="width:32px;height:32px;font-size:.85rem;flex-shrink:0">{{ substr($a->user->name,0,1) }}</div>
                        <div>
                            <strong>{{ $a->user->name }}</strong>
                            <small style="color:var(--gray-400);display:block">{{ $a->user->email }}</small>
                        </div>
                        <span style="color:var(--gray-400)">→</span>
                        <span style="font-weight:600;color:var(--primary)">{{ $a->espace->nom }}</span>
                        <span style="color:#f59e0b;font-size:1rem">{{ str_repeat('★', $a->note) }}{{ str_repeat('☆', 5-$a->note) }}</span>
                        <span class="cw-statut-badge {{ $a->valide ? 'confirmee' : 'en_attente' }}">
                            {{ $a->valide ? __('messages.admin_avis_valides') : __('messages.admin_avis_en_attente') }}
                        </span>
                    </div>
                    @if($a->titre)
                        <p style="font-weight:600;margin-bottom:.25rem">"{{ $a->titre }}"</p>
                    @endif
                    @if($a->commentaire)
                        <p style="color:var(--gray-600);font-size:.9rem;font-style:italic">"{{ $a->commentaire }}"</p>
                    @endif
                    <small style="color:var(--gray-400)">{{ $a->created_at->format('d/m/Y à H:i') }}</small>
                </div>
                <div style="display:flex;gap:.5rem;flex-shrink:0">
                    @if(!$a->valide)
                    <button wire:click="valider({{ $a->id }})" class="cw-btn cw-btn-success cw-btn-xs" title="{{ __('messages.admin_avis_valider') }}">
                        <i class="fas fa-check"></i>
                    </button>
                    @else
                    <button wire:click="rejeter({{ $a->id }})" class="cw-btn cw-btn-outline cw-btn-xs" title="{{ __('messages.admin_avis_rejeter') }}">
                        <i class="fas fa-ban"></i>
                    </button>
                    @endif
                    <button wire:click="confirmDelete({{ $a->id }})" class="cw-btn cw-btn-danger cw-btn-xs" title="{{ __('messages.admin_avis_supprimer') }}">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
        @empty
        <div style="text-align:center;padding:3rem;color:var(--gray-400)">
            <i class="fas fa-comments" style="font-size:2rem;margin-bottom:1rem;display:block"></i>
            {{ __('messages.admin_avis_aucun') }}
        </div>
        @endforelse
    </div>
    <div style="margin-top:1rem">{{ $avis->links() }}</div>

    {{-- Modal Supprimer --}}
    @if($showDeleteModal)
    <div class="cw-modal-overlay" wire:click.self="$set('showDeleteModal', false)">
        <div class="cw-modal" style="max-width:420px">
            <div class="cw-modal-header">
                <h3 style="color:#ef4444"><i class="fas fa-exclamation-triangle"></i> {{ __('messages.admin_avis_supprimer_titre') }}</h3>
                <button wire:click="$set('showDeleteModal', false)" class="cw-modal-close"><i class="fas fa-times"></i></button>
            </div>
            <div class="cw-modal-body"><p>{{ __('messages.admin_avis_supprimer_irreversible') }}</p></div>
            <div class="cw-modal-footer">
                <button wire:click="$set('showDeleteModal', false)" class="cw-btn cw-btn-outline">{{ __('messages.annuler') }}</button>
                <button wire:click="delete" class="cw-btn cw-btn-danger"><i class="fas fa-trash"></i> {{ __('messages.supprimer') }}</button>
            </div>
        </div>
    </div>
    @endif
</div>