@extends('layouts.app')
@section('title', 'Admin — Avis')
@section('content')
<div class="cw-page-header">
    <div class="cw-container">
        <h1><i class="fas fa-star"></i> Gestion des avis</h1>
        <p>Validez et modérez les avis des utilisateurs</p>
    </div>
</div>
<div class="cw-container" style="padding-bottom:4rem">
    @include('admin.partials.nav')
    @livewire('admin-avis')
</div>
@endsection

