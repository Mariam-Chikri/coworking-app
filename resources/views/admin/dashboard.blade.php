@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
<div class="cw-page-header">
    <div class="cw-container">
        <h1><i class="fas fa-shield-alt"></i> Admin</h1>
        <p>{{ app()->getLocale() === 'en' ? 'Full control over the platform' : 'Contrôle complet de la plateforme' }}</p>
    </div>
</div>
<div class="cw-container" style="padding-bottom:4rem">
    @include('admin.partials.nav')
    @livewire('admin-dashboard')
</div>
@endsection

