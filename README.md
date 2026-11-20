# MoCU Graduation Companion

Mobile-first graduation companion for **Moshi Co-operative University (MoCU)**.

Provides ceremony lists, best students, timetable, venues, university leaders, gallery memories, live streams, and help contacts.

## Stack

- PHP 8.3+ / Laravel 12
- Blade views (`resources/views/SmartGradu_App`)
- Front-end assets (`public/SmartGradu_App/assets`)
- JSON data store (`storage/app/graduation`)

## Application structure

| Path | Purpose |
|------|---------|
| `app/Http/Controllers/Graduation` | Ceremony page endpoints |
| `app/Http/Controllers/Auth` | Login / logout |
| `app/Services/GraduationData.php` | JSON data access |
| `routes/web.php` | Public web routes |
| `resources/views/SmartGradu_App` | UI templates |
| `public/SmartGradu_App/assets` | CSS, JS, images |
| `public/SmartGradu_App/uploads/gallery` | Gallery photo uploads |

## JSON data (`storage/app/graduation`)

| File | Purpose |
|------|---------|
| `graduands.json` | Graduands / best-student source list |
| `locations.json` | Venue Guide map points |
| `leaders.json` | University leaders |
| `gallery.json` | Gallery memory index |
| `timetable.json` | Ceremony timetable |
| `live.json` | Live stream config |
| `help.json` | Help contacts / FAQs |

Login still uses the `users` table. Ceremony list content is JSON-based.

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
4. Ensure `storage/app/graduation/*.json` is present on the server.
5. Ensure `public/SmartGradu_App/uploads/gallery` is writable for gallery uploads.
