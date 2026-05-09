@extends('tablar::page')
@section('title', 'Editar periodo académico')
@section('content')
<div class="page-header d-print-none"><div class="container-xl"><div class="row g-2 align-items-center"><div class="col"><h2 class="page-title">Editar periodo: {{ $academicPeriod->name }}</h2></div><div class="col-auto ms-auto d-print-none"><a href="{{ route('academic-periods.show', $academicPeriod) }}" class="btn btn-outline-secondary">Ver detalle</a></div></div></div></div>
<div class="page-body"><div class="container-xl">@if(config('tablar.display_alert'))@include('tablar::common.alert')@endif<div class="row g-3"><div class="col-12 col-lg-8"><div class="card"><div class="card-header"><h3 class="card-title">Actualizar información</h3></div><div class="card-body"><form method="POST" action="{{ route('academic-periods.update', $academicPeriod) }}">@csrf @method('PUT') @include('academic-periods.form')</form></div></div></div></div></div></div>
@endsection
