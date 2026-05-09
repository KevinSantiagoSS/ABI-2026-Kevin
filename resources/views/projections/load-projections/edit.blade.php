@extends('tablar::page')

@section('title', 'Editar proyeccion de carga')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('projections.load-projections.index') }}">Proyeccion de carga</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Editar</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">Editar proyeccion de carga</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('projections.load-projections.update', $loadProjection) }}">
                        @csrf
                        @method('PUT')
                        @include('projections.load-projections.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
