<div>
    <button wire:click="toggle" class="cw-chatbot-toggle" aria-label="{{ __('messages.chatbot_titre') }}">
        @if($ouvert)
            <i class="fas fa-times"></i>
        @else
            <i class="fas fa-robot"></i>
        @endif
    </button>

    @if($ouvert)
    <div class="cw-chatbot-window">
        <div class="cw-chatbot-header">
            <div class="cw-avatar" style="background:rgba(255,255,255,.2)">🤖</div>
            <div>
                <h4>{{ __('messages.chatbot_titre') }}</h4>
                <span style="font-size:.75rem;opacity:.8">{{ app()->getLocale() === 'en' ? 'Online' : 'En ligne' }}</span>
            </div>
        </div>
        <div class="cw-chatbot-body" id="chatbot-body">
            @foreach($historique as $msg)
            <div class="cw-chatbot-msg {{ $msg['role'] }}">
                {{ $msg['message'] }}
            </div>
            @endforeach
        </div>
        <div class="cw-chatbot-footer">
            <input wire:model="question"
                   wire:keydown.enter="envoyer"
                   type="text"
                   placeholder="{{ __('messages.chatbot_placeholder') }}"
                   autocomplete="off">
            <button wire:click="envoyer" title="{{ __('messages.chatbot_envoyer') }}">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
document.addEventListener('livewire:update', () => {
    const body = document.getElementById('chatbot-body');
    if (body) body.scrollTop = body.scrollHeight;
});
</script>
@endpush
