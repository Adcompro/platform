# 🔧 Migration Cleanup Guide

## Probleem
De 142 migration files hebben volgorde-problemen, duplicates en missing dependencies waardoor fresh installs falen.

## Oplossing
We gebruiken een **minimale migration set** voor betrouwbare fresh installs.

## Schone Installatie (Aanbevolen)

### Stap 1: Verplaats oude migrations
```bash
# Maak archive directory
mkdir -p database/migrations_archive

# Verplaats ALLE migrations behalve system + comprehensive
find database/migrations -name "*.php" \
  ! -name "0001_*" \
  ! -name "2025_10_02_100332_create_complete_database_schema.php" \
  -exec mv {} database/migrations_archive/ \;
```

### Stap 2: Run migrations
```bash
php artisan migrate:fresh --seed
```

### Stap 3: Verifieer
```bash
php artisan migrate:status
mysql -u user -p database -e "SHOW TABLES;" | wc -l
# Moet 70+ tabellen tonen
```

## Wat blijft over?

**4 Migration Files:**
1. `0001_01_01_000000_create_users_table.php` (Laravel system)
2. `0001_01_01_000001_create_cache_table.php` (Laravel system)
3. `0001_01_01_000002_create_jobs_table.php` (Laravel system)
4. `2025_10_02_100332_create_complete_database_schema.php` (All AdCompro tables)

**1 Seeder:**
- `DatabaseSeeder.php` - Basis data (theme, eerste user, settings)

## Missing Tables Fix (indien nodig)

Als er na migratie nog missing tables zijn:

```bash
# Voeg simplified_theme_settings toe
php artisan tinker --execute="
Schema::create('simplified_theme_settings', function(\$table) {
    \$table->id();
    \$table->unsignedBigInteger('company_id')->nullable();
    \$table->string('primary_color', 7)->default('#3b82f6');
    \$table->string('secondary_color', 7)->default('#64748b');
    \$table->enum('font_size_base', ['10px','11px','12px','13px','14px','15px','16px'])->default('14px');
    \$table->enum('table_header_style', ['light','dark','colored','bold'])->default('light');
    \$table->boolean('is_active')->default(true);
    \$table->timestamps();
});
"
```

## Voordelen

✅ Betrouwbare fresh installs
✅ Geen volgorde-problemen
✅ Geen duplicates
✅ Sneller (1 migration vs 142)
✅ Makkelijker te onderhouden

## Oude Migrations

Alle oude migrations staan in `database/migrations_archive/` voor referentie.

---
**Versie**: 1.0
**Datum**: 02-10-2025
**Getest op**: MySQL 8.0, Laravel 12, PHP 8.3
