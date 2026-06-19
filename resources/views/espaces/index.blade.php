@extends('layouts.app')
@section('title', __('messages.espaces'))
@section('content')
<div class="cw-page-header">
    <div class="cw-container">
        <h1>{{ __('messages.espaces') }}</h1>
        <p>{{ app()->getLocale() === 'en' ? 'Find and book your ideal workspace' : 'Trouvez et réservez votre espace de travail idéal' }}</p>
    </div>
</div>
<div class="cw-container" style="padding-bottom:4rem">
    @livewire('espaces-list')
</div>
@endsection
