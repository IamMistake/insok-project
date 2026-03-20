# Laravel aplikacija za rezervacija na uslugi

Ovoj proekt pretstavuva aplikacija za **rezervacija na uslugi so kalendar i pregled na slobodni termini**.

## Funkcionalnosti

- Najava i registracija so Laravel Breeze (Blade).
- Ulogi na korisnici:
  - `admin` - upravuva so uslugi, rabotno vreme, blokirani termini i kalendar.
  - `client` - pregled na slobodni termini, rezervacija, prezakazuvanje i otkazuvanje.
- Kalendar prikaz so FullCalendar za:
  - klientski rezervacii,
  - admin pregled na site rezervacii i blokadi.
- Generator na slobodni termini baziran na:
  - rabotno vreme,
  - trajanje na usluga,
  - postoecki rezervacii,
  - blokirani periodi,
  - povtorlivi blokirani termini.
- Email notifikacii pri kreiranje, otkazuvanje i prezakazuvanje na rezervacija.
- Validacii za edge cases: bez preklopuvanje, bez termini nadvor od rabotno vreme, bez termini vo blokirani intervali.

## Tehnologii

- Laravel 13
- PHP 8.5+
- SQLite
- Laravel Breeze (Blade)
- Tailwind CSS
- FullCalendar (CDN)

## Instalacija

1. Instaliraj zavisnosti:

```bash
composer install
npm install
```

2. Konfiguriraj `.env` (vekje e postaveno za SQLite).

3. Generiraj kluc i pripremi baza:

```bash
php artisan key:generate
php artisan migrate:fresh --seed
```

4. Startuvaj ja aplikacijata:

```bash
php artisan serve
npm run dev
```

## Demo korisnici

Po `--seed` se kreiraat slednite korisnici:

- Admin:
  - email: `admin@example.com`
  - password: `password`
- Client:
  - email: `client@example.com`
  - password: `password`

## Glavni URL ruti

- `/calendar` - klientski kalendar i forma za rezervacija.
- `/admin/calendar` - admin kalendar.
- `/admin/services` - upravuvanje so uslugi.
- `/admin/business-hours` - rabotno vreme.
- `/admin/blocked-periods` - blokirani termini.
- `/admin/recurring-blocked-periods` - povtorlivi blokirani termini.

## Testiranje

```bash
php artisan test
```

Testovite pokrivaat osnoven booking flow, preklop na termini i role access restrikcii.
