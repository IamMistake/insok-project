<nav x-data="{ open: false }" class="rr-container rr-topbar relative z-50">
    <div class="rr-header-card">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('dashboard') }}" class="rr-brand">
                    <span class="rr-brand-mark">R</span>
                    <span class="rr-brand-copy">
                        <span class="rr-brand-title">ReserveRight</span>
                        <span class="rr-brand-subtitle">Booking Platform</span>
                    </span>
                </a>

                <button @click="open = ! open" class="inline-flex items-center justify-center rounded-full border border-[color:var(--rr-line)] bg-[rgba(255,255,255,0.5)] p-2 text-[color:var(--rr-muted)] sm:hidden">
                    <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="hidden flex-1 items-center justify-between gap-6 sm:flex">
                <div class="flex flex-wrap items-center gap-2">
                    @if (Auth::user()->role === \App\Models\User::ROLE_ADMIN)
                        <x-nav-link :href="route('admin.calendar.index')" :active="request()->routeIs('admin.calendar.*')">{{ __('Admin calendar') }}</x-nav-link>
                        <x-nav-link :href="route('admin.services.index')" :active="request()->routeIs('admin.services.*')">{{ __('Services') }}</x-nav-link>
                        <x-nav-link :href="route('admin.business-hours.index')" :active="request()->routeIs('admin.business-hours.*')">{{ __('Business hours') }}</x-nav-link>
                        <x-nav-link :href="route('admin.blocked-periods.index')" :active="request()->routeIs('admin.blocked-periods.*')">{{ __('Blocked periods') }}</x-nav-link>
                        <x-nav-link :href="route('admin.recurring-blocked-periods.index')" :active="request()->routeIs('admin.recurring-blocked-periods.*')">{{ __('Recurring blocks') }}</x-nav-link>
                    @else
                        <x-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*') || request()->routeIs('bookings.*')">{{ __('My calendar') }}</x-nav-link>
                    @endif
                </div>

                <div class="flex items-center gap-4">
                    <div class="hidden text-right lg:block">
                        <div class="text-sm font-medium text-[color:var(--rr-text)]">{{ Auth::user()->name }}</div>
                        <div class="text-xs uppercase tracking-[0.12em] text-[color:var(--rr-muted)]">{{ Auth::user()->email }}</div>
                    </div>

                    <x-dropdown align="right" width="56">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-3 rounded-full border border-[color:var(--rr-line)] bg-[rgba(255,255,255,0.52)] px-4 py-2 text-sm font-medium text-[color:var(--rr-text)] transition hover:bg-[rgba(159,122,75,0.08)] focus:outline-none">
                                <span>{{ __('Account') }}</span>
                                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-4 pb-2 pt-1 text-xs uppercase tracking-[0.14em] text-[color:var(--rr-muted)]">{{ Auth::user()->email }}</div>
                            <div class="space-y-1 px-2 pb-2">
                                <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-dropdown-link>
                                </form>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>
            </div>
        </div>

        <div x-show="open" x-transition class="space-y-3 pt-4 sm:hidden" style="display: none;">
            <div class="space-y-2">
                @if (Auth::user()->role === \App\Models\User::ROLE_ADMIN)
                    <x-responsive-nav-link :href="route('admin.calendar.index')" :active="request()->routeIs('admin.calendar.*')">{{ __('Admin calendar') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.services.index')" :active="request()->routeIs('admin.services.*')">{{ __('Services') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.business-hours.index')" :active="request()->routeIs('admin.business-hours.*')">{{ __('Business hours') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.blocked-periods.index')" :active="request()->routeIs('admin.blocked-periods.*')">{{ __('Blocked periods') }}</x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('admin.recurring-blocked-periods.index')" :active="request()->routeIs('admin.recurring-blocked-periods.*')">{{ __('Recurring blocks') }}</x-responsive-nav-link>
                @else
                    <x-responsive-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*') || request()->routeIs('bookings.*')">{{ __('My calendar') }}</x-responsive-nav-link>
                @endif
            </div>

            <div class="rounded-2xl border border-[color:var(--rr-line)] bg-[rgba(255,255,255,0.48)] p-4">
                <div class="text-sm font-medium text-[color:var(--rr-text)]">{{ Auth::user()->name }}</div>
                <div class="mt-1 text-xs uppercase tracking-[0.12em] text-[color:var(--rr-muted)]">{{ Auth::user()->email }}</div>
                <div class="mt-4 space-y-2">
                    <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-responsive-nav-link>
                    </form>
                </div>
            </div>
        </div>
    </div>
</nav>
