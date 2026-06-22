@extends('layouts.app')
@section('title', 'Admin — Utilisateurs')
@section('content')
<div class="cw-page-header">
    <div class="cw-container">
        <h1><i class="fas fa-users"></i> Gestion des utilisateurs</h1>
        <p>Consultez et gérez les comptes utilisateurs</p>
    </div>
</div>
<div class="cw-container" style="padding-bottom:4rem">
    @include('admin.partials.nav')
    @livewire('admin-utilisateurs')
</div>
@endsection

