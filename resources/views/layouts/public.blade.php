<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    @php
        $pageTitle       = filled($title ?? null) ? $title.' | theindex.fyi' : 'theindex.fyi — The index of indie web indexes';
        $pageDescription = $description ?? 'A maintained, canonical meta-index of indie web and small web index sites. ' . \App\Models\Index::count() . ' entries across 6 categories.';
        $pageUrl         = url()->current();
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}" />
    <link rel="canonical" href="{{ $pageUrl }}" />

    {{-- Open Graph --}}
    <meta property="og:type"        content="website" />
    <meta property="og:site_name"   content="theindex.fyi" />
    <meta property="og:title"       content="{{ $pageTitle }}" />
    <meta property="og:description" content="{{ $pageDescription }}" />
    <meta property="og:url"         content="{{ $pageUrl }}" />

    <meta property="og:image"         content="{{ asset('og.png') }}" />

    {{-- Twitter / X --}}
    <meta name="twitter:card"        content="summary_large_image" />
    <meta name="twitter:title"       content="{{ $pageTitle }}" />
    <meta name="twitter:description" content="{{ $pageDescription }}" />
    <meta name="twitter:image"       content="{{ asset('og.png') }}" />

    <script>
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        }
    </script>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-stone-100 dark:bg-stone-900 text-stone-900 dark:text-stone-100 antialiased transition-colors duration-200">

    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-3 focus:left-3 focus:z-50 focus:rounded focus:bg-white dark:focus:bg-stone-800 focus:px-4 focus:py-2 focus:text-sm focus:font-medium focus:shadow">
        Skip to main content
    </a>

    <header class="border-b border-stone-200 dark:border-stone-700 py-4 border-t-4 border-t-teal-500">
        <div class="mx-auto max-w-3xl px-4 flex items-center justify-between gap-4">
            <a href="{{ route('home') }}" class="font-mono text-base font-semibold tracking-tight text-teal-700 dark:text-teal-400 hover:text-teal-900 dark:hover:text-teal-300 transition-colors">
                theindex.fyi
            </a>
            <div class="flex items-center gap-5">
                <nav aria-label="Main navigation" class="flex gap-5 text-sm text-stone-500 dark:text-stone-400">
                    <a href="{{ route('home') }}"
                       class="transition-colors {{ request()->routeIs('home') ? 'text-teal-700 dark:text-teal-400 font-medium' : 'hover:text-teal-700 dark:hover:text-teal-400' }}"
                       {{ request()->routeIs('home') ? 'aria-current=page' : '' }}>index</a>
                    <a href="{{ route('submit') }}"
                       class="transition-colors {{ request()->routeIs('submit*') ? 'text-teal-700 dark:text-teal-400 font-medium' : 'hover:text-teal-700 dark:hover:text-teal-400' }}"
                       {{ request()->routeIs('submit*') ? 'aria-current=page' : '' }}>submit</a>
                    <a href="{{ route('about') }}"
                       class="transition-colors {{ request()->routeIs('about') ? 'text-teal-700 dark:text-teal-400 font-medium' : 'hover:text-teal-700 dark:hover:text-teal-400' }}"
                       {{ request()->routeIs('about') ? 'aria-current=page' : '' }}>about</a>
                </nav>

                <button
                    id="theme-toggle"
                    aria-label="Toggle dark mode"
                    class="text-stone-400 dark:text-stone-500 hover:text-stone-600 dark:hover:text-stone-300 transition-colors"
                    onclick="
                        if (document.documentElement.classList.toggle('dark')) {
                            localStorage.theme = 'dark'
                        } else {
                            localStorage.theme = 'light'
                        }
                    "
                >
                    {{-- Moon: shown in light mode --}}
                    <svg class="block dark:hidden w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                    {{-- Sun: shown in dark mode --}}
                    <svg class="hidden dark:block w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                </button>
            </div>
        </div>
    </header>

    <main id="main-content" class="mx-auto max-w-3xl px-4 py-10">
        {{ $slot }}
    </main>

    <footer class="border-t border-stone-200 dark:border-stone-700 py-6 mt-10">
        <div class="mx-auto max-w-3xl px-4 text-sm text-stone-400 dark:text-stone-500 flex justify-between items-center">
            <span>theindex.fyi, a meta-index of the indie web</span>
            <a href="{{ route('submit') }}" class="hover:text-teal-700 dark:hover:text-teal-400 transition-colors">suggest an entry <span aria-hidden="true">→</span></a>
        </div>
    </footer>

</body>
</html>
