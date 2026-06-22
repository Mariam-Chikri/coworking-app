@extends('layouts.app')
@section('title', 'Admin — Espaces')
@section('content')
<div class="cw-page-header">
    <div class="cw-container">
        <h1><i class="fas fa-building"></i> Gestion des espaces</h1>
        <p>Ajoutez, modifiez et gérez les espaces de coworking</p>
    </div>
</div>
<div class="cw-container" style="padding-bottom:4rem">
    @include('admin.partials.nav')
    @livewire('admin-espaces')
</div>
@endsection

