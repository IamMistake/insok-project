<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|domine:400,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="rr-page-shell min-h-screen flex items-center justify-center px-4 py-10 sm:px-6 lg:px-8">
            <div class="w-full max-w-5xl grid gap-8 lg:grid-cols-[minmax(0,0.95fr)_minmax(24rem,0.85fr)] items-center">
                <div class="hidden lg:block pr-8">
                    <div class="rr-kicker mb-4">RezerviRaj</div>
                    <h1 class="rr-display text-5xl leading-[0.95] text-[var(--rr-text)]">Scheduling with a calm point of view.</h1>
                    <p class="mt-6 max-w-xl text-base leading-8 rr-muted">
                        Log in to manage appointments, availability, confirmations, and updates in a space designed to feel precise, warm, and quietly premium.
                    </p>
                    <div class="mt-8 flex items-center gap-4 text-sm rr-muted">
                        <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-[var(--rr-accent)]"></span> elegant booking flow</span>
                        <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-[rgba(159,122,75,0.45)]"></span> admin scheduling control</span>
                    </div>
                </div>

                <div>
                    <a href="/" class="rr-brand mb-5 inline-flex">
                        <span class="rr-brand-mark">R</span>
                        <span class="rr-brand-copy">
                            <span class="rr-brand-title">RezerviRaj</span>
                            <span class="rr-brand-subtitle">Booking App</span>
                        </span>
                    </a>

                    <div class="rr-panel w-full">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
