@extends('layouts.app')
@section('title', 'Admin — Réservations')
@section('content')
<div class="cw-page-header">
    <div class="cw-container">
        <h1><i class="fas fa-calendar-alt"></i> Gestion des réservations</h1>
        <p>Consultez, confirmez et gérez toutes les réservations</p>
    </div>
</div>
<div class="cw-container" style="padding-bottom:4rem">
    @include('admin.partials.nav')
    @livewire('admin-reservations')
</div>
@endsection

