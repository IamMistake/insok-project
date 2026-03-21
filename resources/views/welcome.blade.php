<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ReserveRight</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|domine:400,700&display=swap" rel="stylesheet" />
        <style>
            :root {
                --bg: #f6f2ea;
                --surface: rgba(255, 255, 255, 0.72);
                --surface-strong: rgba(255, 255, 255, 0.88);
                --text: #171717;
                --muted: #5f5a52;
                --line: rgba(23, 23, 23, 0.12);
                --accent: #9f7a4b;
                --accent-soft: rgba(159, 122, 75, 0.14);
                --shadow: 0 24px 60px rgba(30, 24, 18, 0.08);
            }

            * {
                box-sizing: border-box;
            }

            html {
                scroll-behavior: smooth;
            }

            body {
                margin: 0;
                min-height: 100vh;
                font-family: 'Instrument Sans', sans-serif;
                color: var(--text);
                background:
                    radial-gradient(circle at top left, rgba(159, 122, 75, 0.14), transparent 28%),
                    radial-gradient(circle at 85% 20%, rgba(23, 23, 23, 0.05), transparent 22%),
                    linear-gradient(180deg, #faf7f1 0%, var(--bg) 100%);
            }

            .page-shell {
                position: relative;
                overflow: hidden;
            }

            .page-shell::before,
            .page-shell::after {
                content: '';
                position: absolute;
                border: 1px solid var(--line);
                pointer-events: none;
            }

            .page-shell::before {
                width: 16rem;
                height: 16rem;
                top: 6rem;
                right: -4rem;
                border-radius: 50%;
            }

            .page-shell::after {
                width: 28rem;
                height: 28rem;
                left: -18rem;
                top: 24rem;
                transform: rotate(18deg);
            }

            .container {
                width: min(1120px, calc(100% - 2rem));
                margin: 0 auto;
            }

            .topbar {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 1.5rem 0;
            }

            .brand {
                display: inline-flex;
                align-items: center;
                gap: 0.85rem;
                text-decoration: none;
                color: var(--text);
            }

            .brand-mark {
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 50%;
                border: 1px solid var(--line);
                display: grid;
                place-items: center;
                background: rgba(255, 255, 255, 0.55);
                box-shadow: inset 0 0 0 0.35rem rgba(159, 122, 75, 0.08);
            }

            .brand-copy {
                display: grid;
                gap: 0.1rem;
            }

            .brand-title {
                font-size: 0.95rem;
                font-weight: 700;
                letter-spacing: 0.16em;
                text-transform: uppercase;
            }

            .brand-subtitle {
                font-size: 0.75rem;
                color: var(--muted);
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .topbar-links {
                display: flex;
                gap: 0.9rem;
                align-items: center;
            }

            .hero {
                padding: 3.5rem 0 5.5rem;
                display: grid;
                grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
                gap: 2rem;
                align-items: end;
            }

            .eyebrow {
                display: inline-flex;
                align-items: center;
                gap: 0.7rem;
                font-size: 0.78rem;
                letter-spacing: 0.16em;
                text-transform: uppercase;
                color: var(--muted);
                margin-bottom: 1.4rem;
            }

            .eyebrow::before {
                content: '';
                width: 3.5rem;
                height: 1px;
                background: rgba(159, 122, 75, 0.65);
            }

            h1 {
                margin: 0;
                max-width: 11ch;
                font-family: 'Domine', serif;
                font-size: clamp(3.1rem, 8vw, 6.2rem);
                line-height: 0.96;
                letter-spacing: -0.05em;
            }

            .hero p {
                margin: 1.4rem 0 0;
                max-width: 38rem;
                font-size: 1.05rem;
                line-height: 1.8;
                color: var(--muted);
            }

            .actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.85rem;
                margin-top: 2rem;
            }

            .button,
            .ghost-button {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                min-width: 9.5rem;
                padding: 0.95rem 1.4rem;
                border-radius: 999px;
                text-decoration: none;
                font-weight: 600;
                transition: transform 180ms ease, box-shadow 180ms ease, background 180ms ease, border-color 180ms ease;
            }

            .button {
                color: #fff;
                background: #1f1c18;
                box-shadow: 0 14px 32px rgba(31, 28, 24, 0.18);
            }

            .ghost-button {
                color: var(--text);
                border: 1px solid var(--line);
                background: rgba(255, 255, 255, 0.56);
            }

            .button:hover,
            .ghost-button:hover {
                transform: translateY(-1px);
            }

            .hero-panel {
                position: relative;
                min-height: 31rem;
                padding: 2rem;
                border: 1px solid rgba(23, 23, 23, 0.08);
                background: linear-gradient(180deg, var(--surface-strong), rgba(255, 255, 255, 0.62));
                box-shadow: var(--shadow);
                backdrop-filter: blur(14px);
            }

            .hero-panel::before {
                content: '';
                position: absolute;
                inset: 1rem;
                border: 1px solid rgba(159, 122, 75, 0.18);
            }

            .grid-lines {
                position: absolute;
                inset: 0;
                background-image:
                    linear-gradient(to right, rgba(23, 23, 23, 0.05) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(23, 23, 23, 0.05) 1px, transparent 1px);
                background-size: 25% 25%;
                mask-image: linear-gradient(180deg, rgba(0, 0, 0, 0.9), transparent 90%);
            }

            .panel-card {
                position: absolute;
                background: rgba(255, 255, 255, 0.85);
                border: 1px solid rgba(23, 23, 23, 0.08);
                box-shadow: 0 18px 40px rgba(29, 22, 15, 0.08);
                backdrop-filter: blur(10px);
            }

            .panel-card.primary {
                width: min(20rem, calc(100% - 4rem));
                top: 3rem;
                left: 2rem;
                padding: 1.5rem;
            }

            .panel-card.secondary {
                width: 11rem;
                right: 2rem;
                bottom: 5rem;
                padding: 1.15rem;
            }

            .panel-card.tertiary {
                width: 13rem;
                left: 4rem;
                bottom: 2rem;
                padding: 1.15rem;
            }

            .panel-label {
                font-size: 0.72rem;
                letter-spacing: 0.14em;
                text-transform: uppercase;
                color: var(--muted);
                margin-bottom: 0.8rem;
            }

            .panel-metric {
                font-family: 'Domine', serif;
                font-size: 2.5rem;
                line-height: 1;
                margin-bottom: 0.6rem;
            }

            .panel-copy {
                color: var(--muted);
                line-height: 1.65;
                font-size: 0.95rem;
            }

            .panel-bar {
                display: flex;
                gap: 0.5rem;
                margin-top: 1rem;
            }

            .panel-bar span {
                display: block;
                height: 0.45rem;
                border-radius: 999px;
                background: var(--accent-soft);
            }

            .panel-bar span:first-child {
                width: 42%;
                background: var(--accent);
            }

            .panel-bar span:nth-child(2) {
                width: 24%;
            }

            .panel-bar span:nth-child(3) {
                width: 16%;
            }

            .section {
                padding: 2.5rem 0 5rem;
            }

            .section-head {
                display: grid;
                grid-template-columns: 10rem 1fr;
                gap: 1rem;
                align-items: start;
                margin-bottom: 2.25rem;
            }

            .section-kicker {
                font-size: 0.78rem;
                letter-spacing: 0.15em;
                text-transform: uppercase;
                color: var(--muted);
            }

            .section-title {
                font-family: 'Domine', serif;
                font-size: clamp(1.9rem, 4vw, 3rem);
                line-height: 1.08;
                margin: 0;
            }

            .section-description {
                margin: 0.85rem 0 0;
                max-width: 44rem;
                color: var(--muted);
                line-height: 1.8;
            }

            .steps,
            .features {
                display: grid;
                gap: 1rem;
            }

            .steps {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .features {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }

            .card {
                position: relative;
                padding: 1.5rem;
                background: linear-gradient(180deg, rgba(255, 255, 255, 0.88), rgba(255, 255, 255, 0.62));
                border: 1px solid rgba(23, 23, 23, 0.08);
                box-shadow: 0 20px 42px rgba(28, 24, 18, 0.06);
            }

            .card::before {
                content: '';
                position: absolute;
                top: 1.1rem;
                right: 1.1rem;
                width: 0.9rem;
                height: 0.9rem;
                border-radius: 50%;
                border: 1px solid rgba(159, 122, 75, 0.5);
            }

            .step-number {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 2.25rem;
                height: 2.25rem;
                border-radius: 50%;
                border: 1px solid rgba(159, 122, 75, 0.32);
                color: var(--accent);
                margin-bottom: 1.1rem;
                font-size: 0.9rem;
                font-weight: 700;
            }

            .card h3 {
                margin: 0;
                font-size: 1.15rem;
            }

            .card p {
                margin: 0.8rem 0 0;
                color: var(--muted);
                line-height: 1.75;
                font-size: 0.95rem;
            }

            .feature-icon {
                width: 2.75rem;
                height: 2.75rem;
                border: 1px solid rgba(159, 122, 75, 0.28);
                display: grid;
                place-items: center;
                margin-bottom: 1.1rem;
                color: var(--accent);
                background: rgba(159, 122, 75, 0.06);
            }

            .site-footer {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                padding: 1.6rem 0 2rem;
                border-top: 1px solid var(--line);
                color: var(--muted);
                font-size: 0.92rem;
            }

            @media (max-width: 980px) {
                .hero,
                .section-head,
                .features,
                .steps,
                .site-footer {
                    grid-template-columns: 1fr;
                }

                .hero {
                    padding-top: 2rem;
                }

                .hero-panel {
                    min-height: 26rem;
                }

                .site-footer {
                    display: grid;
                }
            }

            @media (max-width: 720px) {
                .topbar {
                    flex-direction: column;
                    align-items: flex-start;
                    gap: 1rem;
                }

                .topbar-links,
                .actions {
                    width: 100%;
                }

                .topbar-links a,
                .actions a {
                    flex: 1;
                }

                .hero-panel {
                    min-height: 23rem;
                    padding: 1.25rem;
                }

                .panel-card.primary,
                .panel-card.secondary,
                .panel-card.tertiary {
                    position: absolute;
                }

                .panel-card.primary {
                    top: 1.5rem;
                    left: 1.25rem;
                    width: calc(100% - 2.5rem);
                }

                .panel-card.secondary {
                    right: 1.25rem;
                    bottom: 4.25rem;
                    width: 9.5rem;
                }

                .panel-card.tertiary {
                    left: 1.25rem;
                    bottom: 1.25rem;
                    width: 10.5rem;
                }
            }
        </style>
    </head>
    <body>
        <div class="page-shell">
            <header class="container topbar">
                <a href="/" class="brand">
                    <span class="brand-mark">R</span>
                    <span class="brand-copy">
                        <span class="brand-title">ReserveRight</span>
                        <span class="brand-subtitle">Booking Platform</span>
                    </span>
                </a>

                <nav class="topbar-links">
                    <a class="ghost-button" href="{{ route('login') }}">Log In</a>
                    @if (Route::has('register'))
                        <a class="button" href="{{ route('register') }}">Register</a>
                    @endif
                </nav>
            </header>

            <main class="container">
                <section class="hero">
                    <div>
                        <div class="eyebrow">Quiet precision for modern scheduling</div>
                        <h1>Appointments arranged with calm clarity.</h1>
                        <p>
                            ReserveRight is a refined booking platform where clients reserve appointments in moments and admins manage availability with precision, confidence, and ease.
                        </p>

                        <div class="actions">
                            <a class="button" href="{{ route('login') }}">Log In</a>
                            @if (Route::has('register'))
                                <a class="ghost-button" href="{{ route('register') }}">Register</a>
                            @endif
                        </div>
                    </div>

                    <div class="hero-panel" aria-hidden="true">
                        <div class="grid-lines"></div>

                        <div class="panel-card primary">
                            <div class="panel-label">Booking rhythm</div>
                            <div class="panel-metric">03</div>
                            <div class="panel-copy">Select a service, choose a free slot, confirm instantly.</div>
                            <div class="panel-bar">
                                <span></span><span></span><span></span>
                            </div>
                        </div>

                        <div class="panel-card secondary">
                            <div class="panel-label">Availability</div>
                            <div class="panel-copy">Live calendar visibility without clutter.</div>
                        </div>

                        <div class="panel-card tertiary">
                            <div class="panel-label">Admin flow</div>
                            <div class="panel-copy">Working hours, blocked periods, and updates in one place.</div>
                        </div>
                    </div>
                </section>

                <section class="section" id="how-it-works">
                    <div class="section-head">
                        <div class="section-kicker">How it works</div>
                        <div>
                            <h2 class="section-title">A booking flow reduced to what matters.</h2>
                            <p class="section-description">Every interaction is designed to feel direct and effortless, from first selection to final confirmation.</p>
                        </div>
                    </div>

                    <div class="steps">
                        <article class="card">
                            <div class="step-number">01</div>
                            <h3>Pick a service</h3>
                            <p>Choose the appointment type that fits your need, with durations and options presented clearly.</p>
                        </article>

                        <article class="card">
                            <div class="step-number">02</div>
                            <h3>Choose a slot</h3>
                            <p>Browse available times in a clean calendar view and select a free term without guesswork.</p>
                        </article>

                        <article class="card">
                            <div class="step-number">03</div>
                            <h3>Confirm booking</h3>
                            <p>Lock in the appointment instantly and keep everything organized with a simple confirmation flow.</p>
                        </article>
                    </div>
                </section>

                <section class="section" id="features">
                    <div class="section-head">
                        <div class="section-kicker">Features</div>
                        <div>
                            <h2 class="section-title">Built for elegant control on both sides of the booking.</h2>
                            <p class="section-description">Clients get a smooth reservation experience, while administrators retain full oversight of capacity and scheduling.</p>
                        </div>
                    </div>

                    <div class="features">
                        <article class="card">
                            <div class="feature-icon">01</div>
                            <h3>Calendar view</h3>
                            <p>Free terms and upcoming appointments are presented in a format that stays easy to scan.</p>
                        </article>

                        <article class="card">
                            <div class="feature-icon">02</div>
                            <h3>Instant confirmation</h3>
                            <p>Clients receive immediate clarity once a slot is reserved, with fewer back-and-forth messages.</p>
                        </article>

                        <article class="card">
                            <div class="feature-icon">03</div>
                            <h3>Email reminders</h3>
                            <p>Automated notifications support a dependable booking experience and reduce missed appointments.</p>
                        </article>

                        <article class="card">
                            <div class="feature-icon">04</div>
                            <h3>Easy rescheduling</h3>
                            <p>Changes happen safely, with availability checks that preserve order and prevent conflicts.</p>
                        </article>
                    </div>
                </section>
            </main>

            <footer class="container site-footer">
                <div>ReserveRight</div>
                <div>&copy; {{ now()->year }} ReserveRight. All rights reserved.</div>
            </footer>
        </div>
    </body>
</html>
