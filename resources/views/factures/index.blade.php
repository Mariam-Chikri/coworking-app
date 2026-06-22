@extends('layouts.app')
@section('title', __('messages.mes_factures'))
@section('content')
<div class="cw-page-header">
    <div class="cw-container">
        <h1>{{ __('messages.mes_factures') }}</h1>
        <p>{{ app()->getLocale() === 'en' ? 'Download your invoices as PDF' : 'Téléchargez vos factures en PDF' }}</p>
    </div>
</div>
<div class="cw-container" style="padding-bottom:4rem">
    @php $factures = auth()->user()->factures()->with('reservation.espace')->latest()->paginate(15); @endphp
    @if($factures->isEmpty())
        <div class="cw-empty">
            <i class="fas fa-file-invoice"></i>
            <h3>{{ app()->getLocale() === 'en' ? 'No invoices yet' : 'Aucune facture' }}</h3>
        </div>
    @else
        <div style="display:flex;flex-direction:column;gap:1rem">
            @foreach($factures as $facture)
            <div class="cw-facture-card {{ $facture->statut }}">
                <div>
                    <div class="cw-facture-num">{{ $facture->numero }}</div>
                    <div class="cw-facture-meta">
                        {{ $facture->reservation->espace->nom }}
                        — {{ $facture->date_emission->format('d/m/Y') }}
                    </div>
                    <span class="cw-statut-badge {{ $facture->statut === 'payee' ? 'confirmee' : ($facture->statut === 'emise' ? 'en_attente' : 'annulee') }}"
                          style="margin-top:.4rem">
                        {{ __('messages.facture_'.$facture->statut) }}
                    </span>
                </div>
                <div class="cw-facture-amount">{{ number_format($facture->montant_ttc, 2) }} MAD</div>
                <a href="{{ route('factures.pdf', $facture) }}" target="_blank"
                   class="cw-btn cw-btn-outline cw-btn-sm">
                    <i class="fas fa-file-pdf"></i> {{ __('messages.telecharger_pdf') }}
                </a>
            </div>
            @endforeach
        </div>
        {{ $factures->links() }}
    @endif
</div>
@endsection
