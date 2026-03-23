{{--
Installation Layout - Refined
Content anchored top, no floating footer in wizard
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ trans('config.dir') }}">

<head>
    <meta charset="utf-8">
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>@yield('page_title')</title>
    @vite(['resources/css/app.css', 'resources/js/core.js'])
    <x-meta.favicons />
    <x-ui.brand-styles />
</head>

<body
    class="antialiased bg-white dark:bg-secondary-900 text-secondary-900 dark:text-white min-h-screen"
    x-data="{ tab: 1, installing: false }" x-cloak x-show="true">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside class="hidden lg:flex w-56 flex-col bg-primary-950 flex-shrink-0">
            <div class="flex flex-col h-full p-5">
                @include('installation.includes.sidebar')
            </div>
        </aside>

        {{-- Main Content --}}
        <main class="flex-1 flex flex-col overflow-y-auto">
            {{-- Mobile Header --}}
            <div class="lg:hidden flex items-center justify-between p-4 border-b border-secondary-200 dark:border-secondary-800">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-md bg-primary-600 flex items-center justify-center text-white font-semibold text-xs">
                        {{ substr(config('default.app_name'), 0, 1) }}
                    </div>
                    <span class="font-medium text-sm text-secondary-900 dark:text-white">{{ config('default.app_name') }}</span>
                </div>
                <button type="button" @click="toggleTheme()"
                    class="p-2 rounded-md text-secondary-400 hover:bg-secondary-100 dark:hover:bg-secondary-800 transition-colors">
                    <x-ui.icon icon="sun" class="w-4 h-4 hidden dark:block" />
                    <x-ui.icon icon="moon" class="w-4 h-4 dark:hidden" />
                </button>
            </div>

            {{-- Content area with proper padding --}}
            <div class="p-6 lg:p-10 lg:pt-8">
                <div class="max-w-2xl">
                    @yield('content')
                </div>
            </div>
        </main>
    </div>
</body>

</html>