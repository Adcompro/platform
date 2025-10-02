# 🚀 AdCompro - Enterprise Project Management Platform

**Version**: 1.0  
**Laravel**: 12.32.5  
**PHP**: 8.3+  
**Node**: 18.x+

## 📋 Overview

AdCompro is an enterprise-grade project management and time tracking platform built for marketing agencies, consultancy firms, and software development teams. It features advanced AI capabilities, multi-company support, and comprehensive financial management.

## ✨ Key Features

### 🎯 Project Management
- **4-Level Hierarchy**: Projects → Milestones → Tasks → Subtasks
- **Drag & Drop**: Sortable interface for all levels
- **5-Level Pricing Override**: Flexible pricing at every level
- **Project Templates**: Reusable templates with one-click import
- **Service Catalog**: Standardized services library

### 💼 Multi-Company Support
- **Multi-BV Architecture**: Multiple companies in one system
- **Inter-Company Billing**: Automatic cross-company invoicing
- **Company Isolation**: Perfect data separation
- **Rollover Budgets**: Monthly fee tracking with automatic rollover

### 🤖 AI-Powered Features
- **Time Entry Predictions**: Pattern-based automatic suggestions
- **Invoice Bundeling**: Smart grouping of invoice lines
- **Project Intelligence**: Automated insights and recommendations
- **AI Chat Assistant**: Context-aware help system
- **Weekly Digest**: Automated project summaries
- **Cost Tracking**: Token usage and cost monitoring

### 📅 Calendar Integration
- **Microsoft 365 OAuth**: Direct Outlook synchronization
- **Multi-Provider Support**: Microsoft, Google, Apple (basic)
- **Auto-Convert**: Calendar events → Time entries
- **AI Predictions**: Smart project/task predictions

### 💰 Financial Management
- **Advanced Invoicing**: Draft → Finalized → Sent → Paid workflow
- **Time Tracking**: Approval workflow with role-based access
- **Budget Tracking**: Monthly fees with rollover system
- **Additional Costs**: One-time and recurring costs management
- **Invoice Templates**: Drag & drop template builder

### 🎨 Customization
- **Theme System**: Per-installation themes with live preview
- **White-Label**: Fully customizable branding
- **Accessibility**: Font size control and contrast options

## 🛠️ Tech Stack

- **Backend**: Laravel 12, PHP 8.3
- **Database**: MySQL 8.0
- **Cache/Queue/Sessions**: Redis
- **Frontend**: Alpine.js, Tailwind CSS, Chart.js
- **AI**: OpenAI GPT-4
- **Calendar**: Microsoft Graph API

## 📦 Installation

### Requirements
- PHP 8.2 or higher
- Composer 2.x
- Node.js 18.x or higher
- MySQL 8.0 or higher
- Redis (recommended for production)

### Quick Start

```bash
# 1. Clone repository
git clone https://github.com/yourusername/node.adcompro.app.git
cd node.adcompro.app

# 2. Install dependencies
composer install
npm install

# 3. Configure environment
cp .env.example .env
php artisan key:generate

# 4. Configure database in .env
DB_DATABASE=adcompro
DB_USERNAME=your_username
DB_PASSWORD=your_password

# 5. Run migrations
php artisan migrate --seed

# 6. Build assets
npm run build

# 7. Start server
php artisan serve
```

Visit: http://localhost:8000

## 🔧 Configuration

### Redis Setup (Recommended)

```env
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
REDIS_CLIENT=phpredis
```

### Microsoft Graph OAuth

```env
MSGRAPH_CLIENT_ID=your_client_id
MSGRAPH_CLIENT_SECRET=your_client_secret
MSGRAPH_TENANT_ID=common
MSGRAPH_REDIRECT_URI=https://yourdomain.com/msgraph/oauth
```

### OpenAI Configuration

```env
OPENAI_API_KEY=your_openai_key
OPENAI_ORGANIZATION=your_org_id
```

## 📊 Database Schema

The platform uses 53 active tables organized in:
- System tables (Laravel core)
- Business entities (Companies, Customers, Contacts)
- Project management (Projects, Milestones, Tasks, Subtasks)
- Financial (Invoices, Time Entries, Budgets)
- Service catalog & Templates
- Calendar & Integrations
- AI features

See `DATABASE_MIGRATION_GUIDE.md` for complete schema documentation.

## 🚀 Deployment

### Production Checklist

```bash
# 1. Optimize for production
composer install --optimize-autoloader --no-dev
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Set permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 3. Configure queue worker
php artisan queue:work --daemon

# 4. Setup cron job
* * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
```

### Environment

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
```

## 📚 Documentation

- **User Guide**: `/docs/user-guide.md`
- **API Documentation**: `/docs/api.md`
- **Database Schema**: `DATABASE_MIGRATION_GUIDE.md`
- **Comparison with Harvest**: `ADCOMPRO_VS_HARVEST_COMPARISON.md`
- **Project Documentation**: `CLAUDE.md`

## 🔐 Security

- Role-based access control (5 roles)
- Company-level data isolation
- Soft deletes for data recovery
- Activity logging on all entities
- CSRF protection
- SQL injection prevention
- XSS protection

## 🧪 Testing

```bash
# Run tests
php artisan test

# With coverage
php artisan test --coverage
```

## 🤝 Contributing

This is a private enterprise platform. For internal development:

1. Create feature branch: `git checkout -b feature/new-feature`
2. Commit changes: `git commit -m 'Add new feature'`
3. Push branch: `git push origin feature/new-feature`
4. Create Pull Request

## 📝 License

Proprietary - All Rights Reserved

## 👥 Team

- **Development**: Internal Team
- **AI Integration**: Claude Code Assistant
- **Support**: support@adcompro.app

## 🔄 Changelog

### Version 1.0.0 (2025-10-02)
- Initial release
- Complete migration from progress.adcompro.app
- Redis performance optimization
- AI features suite
- Calendar integration
- Clean codebase (removed deprecated code)

## 📞 Support

For technical support or questions:
- Email: support@adcompro.app
- Documentation: Check `/docs` folder
- Internal: Check `CLAUDE.md` for development context

---

**Built with ❤️ using Laravel 12 & Modern Web Technologies**
