<x-guest-layout>
    <div class="mb-8">
        <div class="rr-kicker mb-3">Verify email</div>
        <h1 class="rr-display text-3xl text-[color:var(--rr-text)]">Confirm your email address.</h1>
    </div>

    <div class="mb-5 text-sm leading-7 rr-muted">
        {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-5 rr-alert-success">
            {{ __('A new verification link has been sent to the email address you provided during registration.') }}
        </div>
    @endif

    <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf

            <div>
                <x-primary-button>
                    {{ __('Resend Verification Email') }}
                </x-primary-button>
            </div>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf

            <button type="submit" class="text-sm rr-link">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>
