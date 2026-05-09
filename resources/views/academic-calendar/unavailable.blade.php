@extends('tablar::page')

@section('title', $title ?? 'Actividad no disponible')

@section('content')
    <div class="page-wrapper">
        <div class="page-body d-flex align-items-center" style="min-height: calc(100vh - 120px);">
            <div class="container-xl">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-10 col-lg-8 col-xl-7">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4 p-md-5 text-center">
                                <div class="mb-4">
                                    <span class="avatar avatar-xl bg-orange-lt text-orange mx-auto" style="width: 72px; height: 72px;">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-lg" width="32" height="32" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                            <path d="M12 9v4" />
                                            <path d="M12 16h.01" />
                                            <path d="M10.29 3.86l-7.55 13.09a1.91 1.91 0 0 0 1.66 2.86h15.1a1.91 1.91 0 0 0 1.66 -2.86l-7.55 -13.09a1.91 1.91 0 0 0 -3.32 0z" />
                                        </svg>
                                    </span>
                                </div>

                                <h1 class="mb-3">{{ $title ?? 'Actividad no disponible' }}</h1>
                                <p class="text-secondary fs-3 mb-3">{{ $message }}</p>

                                @if (! empty($detail))
                                    <p class="text-secondary mb-4">{{ $detail }}</p>
                                @endif

                                <div class="alert alert-warning text-start mx-auto mb-4" role="alert" style="max-width: 720px;">
                                    <div class="fw-semibold mb-1">Consulte el calendario academico institucional</div>
                                    <a href="{{ $calendarUrl }}" target="_blank" rel="noopener noreferrer">{{ $calendarUrl }}</a>
                                </div>

                                <p class="text-secondary mb-4">
                                    Seras redirigido al inicio en <strong id="redirect-countdown">{{ $redirectSeconds ?? 5 }}</strong> segundos.
                                </p>

                                <div class="d-flex justify-content-center gap-2 flex-wrap">
                                    <a href="{{ $redirectUrl }}" class="btn btn-primary">Volver al inicio ahora</a>
                                    <a href="{{ $calendarUrl }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline-secondary">Ver calendario academico</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const redirectSeconds = Number(@json($redirectSeconds ?? 5));
            const redirectUrl = @json($redirectUrl);
            const countdownElement = document.getElementById('redirect-countdown');
            let remainingSeconds = redirectSeconds;

            const intervalId = window.setInterval(function () {
                remainingSeconds -= 1;

                if (countdownElement && remainingSeconds >= 0) {
                    countdownElement.textContent = remainingSeconds;
                }

                if (remainingSeconds <= 0) {
                    window.clearInterval(intervalId);
                    window.location.href = redirectUrl;
                }
            }, 1000);
        });
    </script>
@endpush
