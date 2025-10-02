# 🚀 AdCompro Platform - Fresh Installation Guide

## ✅ Production-Ready Installation

Deze installatie methode is getest en werkt gegarandeerd op verse databases.

## 📋 Requirements

- PHP 8.3+
- MySQL 8.0+
- Composer 2.x
- Node.js 18.x+
- Redis (aanbevolen)

## 🔧 Installatie Stappen

### 1. Clone Repository

```bash
git clone https://github.com/Adcompro/platform.git
cd platform
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# JavaScript dependencies
npm install
npm run build
```

### 3. Environment Configuration

```bash
# Copy example environment
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Configure .env

Pas de volgende variabelen aan:

```env
APP_NAME="AdCompro Platform"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://jouw-domein.nl

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jouw_database
DB_USERNAME=jouw_username
DB_PASSWORD=jouw_password

CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
```

### 5. Database Setup

```bash
# Run migrations and seeders
php artisan migrate:fresh --seed
```

**Dit creëert**:
- ✅ 64 database tabellen
- ✅ Default company (AdCompro BV)
- ✅ Admin user (admin@adcompro.app)
- ✅ Default theme
- ✅ System settings

### 6. Permissions

```bash
# Set correct permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 7. Production Optimization

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

## 🔑 Default Login

Na installatie kun je inloggen met:

- **Email**: admin@adcompro.app
- **Password**: admin123

⚠️ **BELANGRIJK**: Verander het wachtwoord direct na eerste login!

## 📊 Verificatie

Check of alles correct is geïnstalleerd:

```bash
# Check aantal tabellen (moet 64+ zijn)
php artisan tinker --execute="
echo 'Tables: ' . count(DB::select('SHOW TABLES'));
"

# Check migratie status
php artisan migrate:status
```

## 🔄 Cron Job Setup

Voeg toe aan crontab:

```bash
* * * * * cd /path/to/platform && php artisan schedule:run >> /dev/null 2>&1
```

## 🐛 Troubleshooting

### Error: "Table doesn't exist"

```bash
# Re-run migrations
php artisan migrate:fresh --seed --force
```

### Error: "Permission denied"

```bash
# Fix permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Error: "Class not found"

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
composer dump-autoload
```

## 📁 Wat is er veranderd?

### Oude Migrations (Gearchiveerd)

Alle 141 oude migration files zijn verplaatst naar `database/migrations_archive/`.
Deze hadden volgorde-problemen en zijn **niet nodig voor fresh installs**.

### Nieuwe Migrations (Production-Ready)

**4 Files**:
1. `0001_01_01_000000_create_users_table.php` - Laravel system
2. `0001_01_01_000001_create_cache_table.php` - Laravel system
3. `0001_01_01_000002_create_jobs_table.php` - Laravel system
4. `2025_10_02_200000_create_adcompro_schema.php` - **Alle AdCompro tabellen**

### Database Schema

De complete database schema wordt geladen uit `database/schema.sql` (1776 regels).
Dit is een exacte dump van de productie database.

## ✨ Voordelen Nieuwe Setup

- ✅ **Betrouwbaar**: Getest op verse databases
- ✅ **Snel**: 1 migration vs 141
- ✅ **Geen volgorde-problemen**: Schema dump heeft juiste volgorde
- ✅ **Production-ready**: Direct klaar voor gebruik
- ✅ **Makkelijk onderhoud**: Minder complexiteit

## 📞 Support

Voor vragen of problemen:
- Check de [CLAUDE.md](CLAUDE.md) voor project documentatie
- Check de [DATABASE_MIGRATION_GUIDE.md](DATABASE_MIGRATION_GUIDE.md) voor database details

---

**Versie**: 2.0
**Datum**: 02-10-2025
**Status**: Production-Ready ✅
