# 🗃️ Database Migration Guide - AdCompro Platform

## 📋 Overzicht

Deze guide beschrijft hoe je een **nieuwe installatie** van de AdCompro Platform kunt opzetten met een **schone database** die alleen de actief gebruikte tabellen bevat.

**Gegenereerd op**: 02-10-2025
**Gebaseerd op**: progress.adcompro.app database schema
**Totaal tabellen**: 53 (actief gebruikte tabellen)

---

## 🚀 Snelle Start - Nieuwe Installatie

### Stap 1: Database Aanmaken

```bash
# Login op MySQL
mysql -u root -p

# Maak nieuwe database aan
CREATE DATABASE adcompro_fresh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Maak database gebruiker aan (optioneel)
CREATE USER 'adcompro_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON adcompro_fresh.* TO 'adcompro_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Stap 2: .env Configureren

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=adcompro_fresh
DB_USERNAME=adcompro_user
DB_PASSWORD=your_secure_password

# Redis configuratie (aanbevolen voor performance)
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Stap 3: Migrations Uitvoeren

Er zijn **twee opties** voor het opzetten van de database:

#### Optie A: Gebruik de Comprehensive Migration (Aanbevolen)

```bash
# Verwijder alle oude migrations (optioneel - voor schone start)
cd database/migrations
mkdir -p ../migrations_backup
mv *.php ../migrations_backup/ 2>/dev/null

# Kopieer alleen de Laravel system migrations terug
mv ../migrations_backup/0001_01_01_000000_create_users_table.php .
mv ../migrations_backup/0001_01_01_000001_create_cache_table.php .
mv ../migrations_backup/0001_01_01_000002_create_jobs_table.php .

# De comprehensive migration staat al in de folder
# 2025_10_02_100332_create_complete_database_schema.php

# Run migrations
cd ../..
php artisan migrate:fresh
```

#### Optie B: Gebruik Alle Bestaande Migrations

```bash
# Run alle 141 migrations (langzamer, maar meer detail)
php artisan migrate:fresh
```

---

## 📊 Database Schema Overzicht

### Systeem Tabellen (Laravel)
- `users` - Gebruikers met role-based access
- `password_reset_tokens` - Password reset tokens
- `cache`, `cache_locks` - Redis cache fallback
- `jobs`, `job_batches`, `failed_jobs` - Queue systeem
- `sessions` - Sessie opslag
- `migrations` - Migration tracking

### Business Entities
- `companies` - Bedrijven (multi-tenant)
- `customers` - Klanten
- `contacts` - Contactpersonen
- `users` - Gebruikers met rollen

### Project Management
- `projects` - Projecten
- `project_milestones` - Milestones
- `project_tasks` - Taken
- `project_subtasks` - Subtaken (NEW - 4-level hierarchy)
- `project_users` - Team members
- `project_companies` - Inter-company billing

### Financial Management
- `time_entries` - Tijdregistratie
- `invoices` - Facturen
- `invoice_lines` - Factuurregels
- `invoice_templates` - Factuur templates
- `project_additional_costs` - Extra kosten
- `project_monthly_additional_costs` - Recurring kosten
- `project_monthly_fees` - Budget tracking
- `monthly_intercompany_charges` - Doorbelasting tussen BV's

### Service Catalog
- `service_categories` - Service categorieën
- `services` - Diensten catalogus
- `service_milestones` - Service milestones
- `service_tasks` - Service taken

### Project Templates
- `project_templates` - Herbruikbare templates
- `template_milestones` - Template milestones
- `template_tasks` - Template taken

### Calendar & Integration
- `calendar_events` - Agenda events
- `user_ms_graph_tokens` - Microsoft 365 OAuth tokens
- `calendar_sync_logs` - Sync logging

### AI Features
- `ai_usage_logs` - AI usage tracking & costs
- `ai_learning_feedback` - Learning feedback
- `ai_settings` - AI configuratie

### Media Campaigns
- `project_media_campaigns` - Media campagnes
- `project_media_mentions` - Media vermeldingen

### Settings & Configuration
- `settings` - System settings (key-value)
- `simplified_theme_settings` - Theme instellingen

---

## 🔧 Database Seeding (Optioneel)

### Stap 1: Maak een Seeder

```bash
php artisan make:seeder InitialDataSeeder
```

### Stap 2: Voeg Basis Data Toe

```php
// database/seeders/InitialDataSeeder.php

public function run()
{
    // 1. Maak eerste company
    $company = Company::create([
        'name' => 'Jouw Bedrijf BV',
        'email' => 'info@jouwbedrijf.nl',
        'default_hourly_rate' => 85.00,
        'is_active' => true,
    ]);

    // 2. Maak super admin user
    User::create([
        'name' => 'Administrator',
        'email' => 'admin@jouwbedrijf.nl',
        'password' => Hash::make('password'),
        'role' => 'super_admin',
        'company_id' => $company->id,
    ]);

    // 3. Basis settings
    Setting::insert([
        ['key' => 'app_timezone', 'value' => 'Europe/Amsterdam', 'type' => 'string'],
        ['key' => 'currency', 'value' => 'EUR', 'type' => 'string'],
        ['key' => 'vat_rate', 'value' => '21', 'type' => 'number'],
    ]);

    // 4. Default theme
    SimplifiedThemeSetting::create([
        'company_id' => null, // Global theme
        'primary_color' => '#3b82f6',
        'secondary_color' => '#64748b',
        'font_size_base' => '14px',
        'table_header_style' => 'light',
        'is_active' => true,
    ]);
}
```

### Stap 3: Run Seeder

```bash
php artisan db:seed --class=InitialDataSeeder
```

---

## ✅ Verificatie

### Check of Alle Tabellen Aangemaakt Zijn

```bash
php artisan tinker
```

```php
// In Tinker:
DB::select('SHOW TABLES');
// Moet 53 tabellen tonen

// Check migrations status
exit();
php artisan migrate:status
```

### Test Database Connectie

```bash
# Via Artisan
php artisan db:show

# Of via MySQL
mysql -u adcompro_user -p adcompro_fresh -e "SHOW TABLES;"
```

---

## 🗑️ Deprecated Tabellen (NIET inbegrepen)

Deze tabellen zijn **NIET** opgenomen in de schone migration omdat ze niet meer gebruikt worden:

- `company_plugins` - Plugin system verwijderd
- `archived_project_subtasks` - Oude archief systeem
- `media_sources` - Oude media implementatie
- `media_ai_analysis_logs` - Vervangen door nieuwe AI logging
- `user_media_mentions` - Oude social media tracking
- `user_media_monitors` - Vervangen door campaigns
- `user_social_mentions` - Geconsolideerd
- `project_social_mentions` - Geconsolideerd
- `social_engagement_metrics` - Oude metrics
- `social_media_mentions` - Oude implementatie
- `social_media_sources` - Oude implementatie

**Totaal verwijderd**: 11 deprecated tabellen

---

## 🔄 Migreren van Bestaande Data

Als je data wilt migreren van `progress_` database naar de nieuwe installatie:

```bash
# 1. Export alleen data (geen structure)
mysqldump -u username -p progress_ \
  --no-create-info \
  --skip-triggers \
  --complete-insert \
  > progress_data_export.sql

# 2. Import in nieuwe database
mysql -u username -p adcompro_fresh < progress_data_export.sql
```

**Let op**: Check foreign key constraints en pas export aan indien nodig.

---

## 📝 Migration Bestanden

### Locatie
```
database/migrations/2025_10_02_100332_create_complete_database_schema.php
```

### Bevat
- 53 actieve tabellen
- Alle foreign key constraints
- Proper indexes
- Correct data types
- Soft deletes waar nodig

### Gebruik

**Fresh install** (aanbevolen voor nieuwe projecten):
```bash
php artisan migrate:fresh
```

**Bestaande installatie** (voegt schema toe aan lege DB):
```bash
php artisan migrate
```

---

## 🚨 Troubleshooting

### Foreign Key Errors

```bash
# Disable foreign key checks tijdelijk
SET FOREIGN_KEY_CHECKS=0;
# Run migrations
SET FOREIGN_KEY_CHECKS=1;
```

### Permission Errors

```bash
# Fix storage permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Character Set Issues

```sql
ALTER DATABASE adcompro_fresh
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
```

---

## 📞 Support

Voor vragen over de database migratie:
- Check `/var/www/vhosts/adcompro.app/progress.adcompro.app/CLAUDE.md` voor project documentatie
- Review de comprehensive migration file voor exacte schema details

---

**Versie**: 1.0
**Laatst bijgewerkt**: 02-10-2025
**Compatibel met**: Laravel 12.x, MySQL 8.0+, PHP 8.2+
