<x-guest-layout>
    <div class="mb-8">
        <div class="rr-kicker mb-3">Welcome back</div>
        <h1 class="rr-display text-3xl text-[color:var(--rr-text)]">Log in to your account.</h1>
        <p class="mt-3 text-sm leading-7 rr-muted">Access appointments, availability, and confirmations in one calm workspace.</p>
    </div>

    <x-auth-session-status class="mb-5" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-[color:var(--rr-line)] text-[color:var(--rr-accent)] shadow-sm focus:ring-[rgba(159,122,75,0.2)]" name="remember">
                <span class="ms-2 text-sm rr-muted">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex flex-col-reverse gap-4 pt-2 sm:flex-row sm:items-center sm:justify-between">
            @if (Route::has('password.request'))
                <a class="text-sm rr-link" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button>
                {{ __('Log in') }}
            </x-primary-button>
        </div>

        @if (Route::has('register'))
            <p class="text-sm rr-muted">Need an account? <a href="{{ route('register') }}" class="rr-link">Create one</a>.</p>
        @endif
    </form>
</x-guest-layout>
