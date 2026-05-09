<!doctype html>
<html lang="{{ Config::get('app.locale') }}" {!! config('tablar.layout') == 'rtl' ? 'dir="rtl"' : '' !!}>
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <script>
        (function () {
            const storageKey = 'tablerTheme';
            const validThemes = ['light', 'dark'];
            const root = document.documentElement;

            function normalizeTheme(theme) {
                return validThemes.includes(theme) ? theme : 'light';
            }

            function readThemeFromUrl() {
                try {
                    const url = new URL(window.location.href);
                    const theme = url.searchParams.get('theme');

                    if (!validThemes.includes(theme)) {
                        return null;
                    }

                    localStorage.setItem(storageKey, theme);
                    url.searchParams.delete('theme');
                    const nextUrl = url.pathname + (url.searchParams.toString() ? '?' + url.searchParams.toString() : '') + url.hash;
                    window.history.replaceState({}, '', nextUrl);

                    return theme;
                } catch (error) {
                    return null;
                }
            }

            function readStoredTheme() {
                try {
                    return localStorage.getItem(storageKey);
                } catch (error) {
                    return null;
                }
            }

            const selectedTheme = normalizeTheme(readThemeFromUrl() ?? readStoredTheme());

            root.setAttribute('data-bs-theme', selectedTheme);
            root.classList.toggle('theme-dark', selectedTheme === 'dark');
            root.style.colorScheme = selectedTheme;

            window.__abiTheme = {
                storageKey: storageKey,
                value: selectedTheme,
            };
        })();
    </script>

    <style>
        html.theme-changing *,
        html.theme-changing *::before,
        html.theme-changing *::after {
            transition: none !important;
        }
    </style>

    {{-- Custom Meta Tags --}}
    @yield('meta_tags')
    {{-- Title --}}
    <title>
        @yield('title_prefix', config('tablar.title_prefix', ''))
        @yield('title', config('tablar.title', 'Tablar'))
        @yield('title_postfix', config('tablar.title_postfix', ''))
    </title>
    <!-- CSS files -->
    @if(config('tablar','vite'))
        @vite('resources/js/app.js')
    @endif
    {{-- Custom Stylesheets (post Tablar) --}}
    @yield('tablar_css')
</head>
@yield('body')
@include('tablar::extra.modal')
@yield('tablar_js')
</html>
