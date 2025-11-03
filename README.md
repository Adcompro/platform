# 🚀 Progress Management Platform

**Enterprise Project Management & Budget Tracking System**

A comprehensive Laravel 12 platform for managing projects, time tracking, budget monitoring, and recurring project series with advanced financial reporting.

---

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Documentation](#documentation)
- [Key Modules](#key-modules)
- [Security](#security)
- [License](#license)

---

## ✨ Features

### Core Functionality
- **Multi-Company Support** - Manage multiple companies with inter-company billing
- **Project Management** - Complete project hierarchy (Projects → Milestones → Tasks → Subtasks)
- **Time Tracking** - Billable/non-billable time entries with approval workflow
- **Budget Tracking** - Real-time budget monitoring with rollover support
- **Recurring Projects** - Series-based project management for monthly retainers
- **Invoice System** - Automated invoice generation with templates
- **Customer Management** - CRM-style customer relationship tracking
- **Team Management** - Cross-company team collaboration

### Advanced Features
- **Budget Rollover** - Unused budget carries forward between months
- **Series Budget View** - Consolidated year-over-year budget tracking
- **Recurring Dashboard** - Overview of all recurring project series
- **Template System** - Project templates for quick project setup
- **Service Catalog** - Pre-configured service packages
- **Additional Costs** - Track one-time and recurring project costs
- **Calendar Integration** - Microsoft 365 calendar sync
- **Role-Based Access** - 5-level permission system

---

## 🛠️ Requirements

- **PHP**: >= 8.2
- **Laravel**: 12.x
- **Database**: MySQL 8.0 or PostgreSQL 15+
- **Node.js**: >= 18.x (for frontend assets)
- **Composer**: Latest version
- **Web Server**: Apache/Nginx with SSL

### PHP Extensions
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML

---

## 📦 Installation

### 1. Clone Repository

```bash
git clone https://github.com/Adcompro/platform.git progress-app
cd progress-app
```

### 2. Install Dependencies

```bash
# PHP dependencies
composer install --no-dev --optimize-autoloader

# Node dependencies
npm install
npm run build
```

### 3. Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database in .env
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Database Migration

```bash
# Run migrations
php artisan migrate --seed

# Optional: Load sample data
php artisan db:seed --class=SampleDataSeeder
```

### 5. Storage Setup

```bash
# Create storage link
php artisan storage:link

# Set permissions
chmod -R 775 storage bootstrap/cache
```

### 6. Configuration

```bash
# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear caches (during development)
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

---

## 📚 Documentation

### Complete System Documentation

**[📄 Budget Tracking System Documentation](docs/BUDGET_TRACKING_SYSTEM.md)**

Complete standalone documentation covering:
- System architecture and core concepts
- Database structure
- Budget calculations (billable hours filtering, rollover logic)
- Recurring dashboard usage
- Series budget views
- User guide for all roles
- Troubleshooting guide

### Development Documentation

**[⚙️ CLAUDE.md](CLAUDE.md)**

Technical development documentation including:
- Coding standards and patterns
- Laravel 12 best practices
- Recent updates and bug fixes
- Database schema details
- Module implementation guides

---

## 🔑 Key Modules

### 1. **Budget Tracking & Recurring Dashboard**

Complete financial tracking with:
- Monthly fee budgets with optional rollover
- Real-time budget vs. actual tracking
- Billable hours filtering (non-billable excluded)
- Visual budget status indicators
- Consolidated recurring series reporting

**Key Features:**
- ✅ Only billable hours count toward budget
- ✅ Rollover is internal shifting (not new money)
- ✅ Per-month budget variations respected
- ✅ All 12 months displayed (including months without projects)
- ✅ Visual consistency (always show remaining OR exceeded, not both)

### 2. **Project Management**

4-level hierarchy:
- **Projects** - Top-level with customer, budget, and billing settings
- **Milestones** - Major deliverables with dates and pricing
- **Tasks** - Detailed work items within milestones
- **Subtasks** - Granular task breakdown

Features:
- Drag & drop reordering
- Template import
- Service package integration
- Team member assignment
- Cross-company collaboration

### 3. **Time Tracking**

Professional time entry system:
- Billable vs. non-billable classification
- Approval workflow (draft → pending → approved/rejected)
- Hourly rate hierarchy (5-level override system)
- Calendar integration (Microsoft 365)
- Bulk approval capabilities

### 4. **Customer & Contact Management**

- Complete CRM functionality
- Contact relationship tracking
- Multiple company relations
- Primary contact designation
- Activity audit trail

### 5. **Invoice System**

- Template-based invoice generation
- Drag & drop invoice builder
- Multiple billing types (monthly, quarterly, milestone, project completion)
- VAT/tax handling
- Draft → finalized → sent → paid workflow

---

## 🔒 Security

### Best Practices Implemented

- **Environment Variables** - All secrets in `.env` (never in Git)
- **Database Encryption** - Sensitive data encrypted at rest
- **CSRF Protection** - Laravel middleware enabled
- **XSS Prevention** - Blade template escaping
- **SQL Injection Prevention** - Eloquent ORM with parameter binding
- **Role-Based Access** - 5-level permission system
- **Company Isolation** - Multi-tenant data separation

### Critical Security Rules

**NEVER commit to Git:**
- `.env` file
- Database credentials
- API keys or tokens
- User passwords
- Log files with sensitive data
- SQL backups

**Environment File (.env)**

```env
# KRITIEK: Gebruik sterke, unieke wachtwoorden!
DB_PASSWORD=your_strong_password_here

# API Keys (genereer je eigen keys)
MSGRAPH_CLIENT_ID=your_ms_graph_client_id
MSGRAPH_CLIENT_SECRET=your_ms_graph_secret
```

---

## 👥 User Roles

| Role | Description | Permissions |
|------|-------------|-------------|
| **super_admin** | System Administrator | Full access to all companies and features |
| **admin** | Company Administrator | Full access within own company |
| **project_manager** | Project Manager | Manage assigned projects and teams |
| **user** | Regular User | Log time, view assigned projects |
| **reader** | Read-Only User | View-only access |

---

## 🎯 Business Rules

### Budget Calculations

1. **Billable Hours Filter (CRITICAL!)**
   - Only `is_billable = 'billable'` AND `status = 'approved'` count toward budget
   - Non-billable hours excluded from all calculations

2. **Rollover in Year Totals**
   - Rollover is internal shifting between months
   - Do NOT add rollover to year totals (causes double counting)

3. **Remaining vs. Exceeded Exclusivity**
   - Always show remaining OR exceeded, never both prominently
   - `remaining = max(0, budget - spent)`
   - `exceeded = max(0, spent - budget)`

4. **Per-Month Budget Variation**
   - Respect different monthly_fee per month in recurring series
   - Use budget from specific active project per month

5. **Percentage Display**
   - No percentage cap for overspent (show real values: 175%, 231%)
   - Use hardcoded hex colors for reliability (not CSS variables)

---

## 🗄️ Database

### Key Tables

- `projects` - Project master data
- `project_milestones` - Milestone tracking
- `project_tasks` - Task management
- `project_subtasks` - Subtask details
- `time_entries` - Time tracking (with billable flag)
- `project_additional_costs` - Additional project costs
- `customers` - Customer CRM data
- `users` - User accounts and authentication
- `companies` - Multi-company support

See [Database Documentation](docs/BUDGET_TRACKING_SYSTEM.md#database-structuur) for complete schema.

---

## 🚀 Development

### Running Locally

```bash
# Start development server
php artisan serve

# Watch frontend assets
npm run dev

# Run queue worker (for background jobs)
php artisan queue:work

# Run scheduler (for cron jobs)
php artisan schedule:work
```

### Testing

```bash
# Run tests
php artisan test

# Run specific test
php artisan test --filter=BudgetTrackingTest
```

### Code Quality

```bash
# PHP CodeSniffer
./vendor/bin/phpcs

# PHP CS Fixer
./vendor/bin/php-cs-fixer fix

# PHPStan
./vendor/bin/phpstan analyse
```

---

## 🤝 Contributing

1. Follow Laravel 12 best practices
2. Write tests for new features
3. Update documentation
4. Use meaningful commit messages
5. Review CLAUDE.md for coding standards

---

## 📞 Support

For technical questions or issues:
- **Documentation**: See `docs/BUDGET_TRACKING_SYSTEM.md`
- **Development Guide**: See `CLAUDE.md`
- **Issues**: Create GitHub issue with detailed description

---

## 📄 License

This project is proprietary software owned by AdCompro BV.

**Copyright © 2025 AdCompro BV. All rights reserved.**

Unauthorized copying, modification, distribution, or use of this software, via any medium, is strictly prohibited without explicit written permission from AdCompro BV.

---

## 🙏 Acknowledgments

Built with:
- [Laravel 12](https://laravel.com/) - PHP Framework
- [Tailwind CSS](https://tailwindcss.com/) - CSS Framework
- [Alpine.js](https://alpinejs.dev/) - JavaScript Framework
- [Livewire](https://laravel-livewire.com/) - Dynamic Interfaces

---

**Last Updated**: November 3, 2025
**Version**: 1.0
**Status**: Production Ready ✅
