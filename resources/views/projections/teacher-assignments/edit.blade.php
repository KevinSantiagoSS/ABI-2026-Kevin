@extends('tablar::page')

@section('title', 'Editar asignacion docente')

@section('content')
    <div class="page-header d-print-none">
        <div class="container-xl">
            <div class="row g-2 align-items-center">
                <div class="col">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('projections.teacher-assignments.index') }}">Asignacion docente</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Editar</li>
                        </ol>
                    </nav>
                    <h2 class="page-title">Editar asignacion docente</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="page-body">
        <div class="container-xl">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('projections.teacher-assignments.update', $teacherAssignment) }}">
                        @csrf
                        @method('PUT')
                        @include('projections.teacher-assignments.form')
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
