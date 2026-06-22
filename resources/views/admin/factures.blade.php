@extends('layouts.app')
@section('title', 'Admin — Factures')
@section('content')
<div class="cw-page-header">
    <div class="cw-container">
        <h1><i class="fas fa-file-invoice"></i> Gestion des factures</h1>
        <p>Suivez les paiements et gérez les factures</p>
    </div>
</div>
<div class="cw-container" style="padding-bottom:4rem">
    @include('admin.partials.nav')
    @livewire('admin-factures')
</div>
@endsection

