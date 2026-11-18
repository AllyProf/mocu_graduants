# MoCU Graduation Companion

Mobile-first graduation companion for **Moshi Co-operative University (MoCU)**.

Provides ceremony lists, best students, timetable, venues, live streams, help contacts, PDF export, and missing-name complaints.

## Stack

- PHP 8.3+ / Laravel 12
- Blade views (`resources/views/SmartGradu_App`)
- Front-end assets (`public/SmartGradu_App/assets`)
- JSON data store (`storage/app/graduation`)
- DomPDF for list export (`barryvdh/laravel-dompdf`)

## Application structure

| Path | Purpose |
|------|---------|
| `app/Http/Controllers/Graduation` | Page and PDF/complaint endpoints |
| `app/Http/Controllers/Auth` | Login / logout |
| `app/Models` | Eloquent models (students, staff, programmes, …) |
| `app/Services/GraduationData.php` | Domain data access (DB + legacy support JSON) |
| `database/migrations` | Schema for academic and people tables |
| `routes/web.php` | Public web routes |
| `resources/views/SmartGradu_App` | UI templates |
| `public/SmartGradu_App/assets` | CSS, JS, images |

## Database (core)

| Table | Purpose |
|-------|---------|
| `users` | Login accounts — **password for staff/admin only** |
| `staff` | Staff profiles (`user_id` links to `users` for password) |
| `students` | Graduands / best students (no password) |
| `programmes` | Programmes linked to academic level |
| `academic_levels` | PhD → Certificate |
| `years_of_study` | First / Second / Third Year |
| `academic_years` | e.g. 2025/2026 |
| `complaints` | Missing-name complaints |

Supporting pages (timetable, venues, live, help) still use JSON under `storage/app/graduation` until migrated.

Seed import source for students/programmes: `database/seeders/data/graduands.json`.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

Default seeded admin (change before production):

- Email: `admin@mocu.ac.tz`
- Password: value of `ADMIN_PASSWORD` in `.env` (default `password`)

## Production notes

1. Set `APP_ENV=production` and `APP_DEBUG=false`.
2. Run `composer install --no-dev --optimize-autoloader`.
3. Set a strong `ADMIN_PASSWORD` and re-seed or update the admin user.
4. Configure real mail settings if complaint email notifications are required.
5. Ensure `storage/` and `bootstrap/cache/` are writable.

## Brand assets

Keep only active images under `public/SmartGradu_App/assets/img`:

- `mocu_logo_nav.png` — navigation / favicon
- `mocu_logo_clear.png` — PDF header / watermark
- `mocu_logo_image.png` — preloader
- `mocu_image2.jpeg` — home countdown banner
- `graduation_image.jpeg` — login background

## Licence

Institutional use for MoCU graduation operations.
