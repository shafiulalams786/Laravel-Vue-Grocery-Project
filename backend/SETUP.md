# 🛠️ Backend Setup Commands

Run these in order from the `backend/` directory.

## 1. Install dependencies
```bash
composer install
```

## 2. Environment file
```bash
cp .env.example .env
php artisan key:generate
```
Then edit `.env` with your DB credentials, Stripe & PayPal keys.

## 3. Database
```bash
# Create database (MySQL)
mysql -u root -p -e "CREATE DATABASE grocery_store;"

# Run migrations
php artisan migrate

# Seed categories, products & admin account
php artisan db:seed
```

## 4. ⚡ Storage symlink (REQUIRED for images)
```bash
php artisan storage:link
```
This creates `public/storage` → `storage/app/public` so uploaded
product/category images are publicly accessible via URL.

**Without this, all product images will show as broken.**

To verify it worked:
```bash
ls -la public/storage
# Should show: public/storage -> ../storage/app/public
```

If you get "The [public/storage] link already exists":
```bash
# Force recreate
php artisan storage:link --force
```

## 5. Start the server
```bash
php artisan serve
# → http://localhost:8000
```

---

## Common artisan commands

| Command | What it does |
|---|---|
| `php artisan storage:link` | Link public/storage to storage/app/public |
| `php artisan storage:link --force` | Recreate the link if it already exists |
| `php artisan migrate` | Run pending migrations |
| `php artisan migrate:fresh --seed` | Drop all tables and re-seed (⚠️ deletes all data) |
| `php artisan db:seed` | Run seeders (categories + products + admin) |
| `php artisan db:seed --class=AdminSeeder` | Re-create admin account only |
| `php artisan route:list` | List all registered routes |
| `php artisan route:list --path=api/admin` | List admin routes only |
| `php artisan config:cache` | Cache config for production |
| `php artisan config:clear` | Clear config cache |
| `php artisan route:clear` | Clear route cache |
| `php artisan cache:clear` | Clear application cache |
| `php artisan optimize:clear` | Clear all caches at once |

---

## Admin credentials

| Field | Value |
|---|---|
| URL | http://localhost:5173/admin |
| Email | admin@freshbasket.com |
| Password | Admin@123 |

> Change immediately in **Admin → Settings → Admin Account**
