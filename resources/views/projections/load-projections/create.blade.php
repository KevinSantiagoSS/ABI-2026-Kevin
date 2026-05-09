@extends('tablar::page')

@section('title', 'Registrar proyeccion de carga')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('projections.load-projections.index') }}">Proyeccion de carga</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Crear</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">Registrar proyeccion de carga</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            @if(! $targetPeriod)
                <div class="alert alert-warning">
                    Debes configurar un periodo academico activo y el siguiente periodo antes de registrar este modulo.
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('projections.load-projections.store') }}">
                            @csrf
                            @include('projections.load-projections.form')
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
