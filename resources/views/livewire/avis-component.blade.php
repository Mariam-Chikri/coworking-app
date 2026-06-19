<div style="border-top:2px solid var(--gray-100);margin-top:1.5rem;padding-top:1.5rem">
    <h4 style="font-size:1rem;font-weight:700;margin-bottom:1rem">
        ⭐ {{ __('messages.donner_avis') }}
    </h4>
    @if($soumis)
        <div class="cw-success-box">
            <i class="fas fa-check-circle"></i>
            <p style="color:#065f46">{{ __('messages.avis_soumis') }}</p>
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:.75rem">
            <div class="cw-stars-input">
                @for($i=1;$i<=5;$i++)
                <span wire:click="$set('note', {{ $i }})" class="{{ $note >= $i ? 'active' : '' }}">★</span>
                @endfor
            </div>
            <div class="cw-field">
                <label>{{ __('messages.votre_commentaire') }}</label>
                <textarea wire:model="commentaire" class="cw-textarea" rows="3" placeholder="{{ __('messages.votre_commentaire') }}"></textarea>
            </div>
            <button wire:click="soumettre" class="cw-btn cw-btn-primary cw-btn-sm">
                <i class="fas fa-paper-plane"></i> {{ __('messages.soumettre_avis') }}
            </button>
        </div>
    @endif
</div>
