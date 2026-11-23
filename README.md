# MoCU Graduation Companion

Mobile-first graduation companion for **Moshi Co-operative University (MoCU)**.

Public ceremony pages: graduand lists, best students, timetable, venues, leadership gallery, photo memories, live stream, help, and voice TTS.

## Stack

- PHP 8.2+ / Laravel 12
- Blade views (`resources/views/SmartGradu_App`)
- Front-end assets (`public/SmartGradu_App/assets`)
- JSON data store (`storage/app/graduation`) — **no database required for the companion**

## Application structure

| Path | Purpose |
|------|---------|
| `app/Http/Controllers/Graduation` | Ceremony page endpoints |
| `app/Services/GraduationData.php` | JSON data access |
| `routes/web.php` | Public web routes |
| `resources/views/SmartGradu_App` | UI templates |
| `public/SmartGradu_App/assets` | CSS, JS, images |
| `public/SmartGradu_App/uploads/gallery` | Gallery photo uploads (writable) |

There is **no staff login**. `/login` and `/logout` redirect home for old bookmarks.

## JSON data (`storage/app/graduation`)

| File | Purpose |
|------|---------|
| `graduands.json` | Graduands list |
| `best-students.json` | Best students list (awards) |
| `programmes.json` | Programme catalogue (filters) |
| `locations.json` | Venue Guide map points |
| `leaders.json` | University leaders |
| `gallery.json` | Gallery memory index |
| `timetable.json` | Ceremony timetable |
| `live.json` | Live stream config |
| `help.json` | Help contacts / FAQs |

Sessions, cache, and queue use **file / sync** drivers by default — MySQL is optional.

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

Open `http://127.0.0.1:8000` (or your XAMPP virtual host).

Optional (only if you want Laravel DB tables / seeders):

```bash
php artisan migrate --seed
```

## Production deploy

1. Copy the project to the server and set `.env`:
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL=https://your-domain.tld`
   - `LOG_LEVEL=error`
   - Keep `SESSION_DRIVER=file`, `CACHE_STORE=file`, `QUEUE_CONNECTION=sync` unless you intentionally use Redis/DB.
2. Generate a unique key: `php artisan key:generate`
3. Install dependencies:

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

4. Ensure these are **writable** by the web user:
   - `storage/`
   - `bootstrap/cache/`
   - `public/SmartGradu_App/uploads/gallery/`
5. Deploy `storage/app/graduation/*.json` with real ceremony data.
6. Point the web root at `public/` (Apache/Nginx).
7. Health check: `GET /up`

### Apache note (XAMPP / shared hosting)

Document root must be `public/`. Do not expose `.env`, `storage/`, or `vendor/` as public URLs.
