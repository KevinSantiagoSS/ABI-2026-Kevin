@extends('tablar::page')
@section('title', 'Registrar periodo académico')
@section('content')
<div class="page-header d-print-none"><div class="container-xl"><div class="row g-2 align-items-center"><div class="col"><h2 class="page-title">Registrar periodo académico</h2><p class="text-muted mb-0">Define el semestre institucional que controlará propuestas, selección y trazabilidad.</p></div><div class="col-auto ms-auto d-print-none"><a href="{{ route('academic-periods.index') }}" class="btn btn-outline-secondary">Volver al listado</a></div></div></div></div>
<div class="page-body"><div class="container-xl">@if(config('tablar.display_alert'))@include('tablar::common.alert')@endif<div class="row g-3"><div class="col-12 col-lg-8"><div class="card"><div class="card-header"><h3 class="card-title">Información general</h3></div><div class="card-body"><form method="POST" action="{{ route('academic-periods.store') }}">@csrf @include('academic-periods.form')</form></div></div></div></div></div></div>
@endsection
