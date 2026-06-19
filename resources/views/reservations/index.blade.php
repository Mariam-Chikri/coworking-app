@extends('layouts.app')
@section('title', __('messages.mes_reservations'))
@section('content')
<div class="cw-page-header">
    <div class="cw-container">
        <h1>{{ __('messages.mes_reservations') }}</h1>
        <p>{{ app()->getLocale() === 'en' ? 'Manage your space bookings' : 'Gérez vos réservations d\'espaces' }}</p>
    </div>
</div>
<div class="cw-container" style="padding-bottom:4rem">
    <div style="display:flex;justify-content:flex-end;margin-bottom:1.5rem">
        <a href="{{ route('espaces.index') }}" class="cw-btn cw-btn-primary">
            <i class="fas fa-plus"></i> {{ __('messages.nouvelle_reservation') }}
        </a>
    </div>
    @livewire('mes-reservations')
</div>
@endsection
