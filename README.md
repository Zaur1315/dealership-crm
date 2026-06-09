# Dealership CRM

Custom CRM system for a multi-dealership automotive group.

## Stack

- Laravel
- PostgreSQL
- Filament
- Blade
- Alpine.js
- Vue/Inertia where needed
- Queues
- Scheduler

## Documentation

- [Project Overview](docs/00-project-overview.md)
- [Local Setup](docs/01-local-setup.md)
- [Git Flow](docs/02-git-flow.md)
- [Database Schema](docs/03-database-schema.md)
- [Auth and Roles](docs/04-auth-and-roles.md)
- [Dealership Context](docs/05-dealership-context.md)
- [User Management](docs/06-user-management.md)
- [Leads Module](docs/07-leads-module.md)
- [Tasks Module](docs/08-tasks-module.md)
- [Notifications](docs/09-notifications.md)
- [Email Module](docs/10-email-module.md)
- [Statistics](docs/11-statistics.md)
- [Production Deployment](docs/12-production-deployment.md)
- [Testing](docs/13-testing.md)
- [Changelog](docs/14-changelog.md)

## Development

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve
```

In another terminal:

```bash
npm install
npm run dev
```

## Git Flow

Main branches:

- `main` — production-ready code
- `dev` — integration branch
- `feature/*` — feature branches from `dev`

## Current Development Stage

Current branch:

```text
feature/project-bootstrap
```

Current goal:

- Bootstrap Laravel project
- Prepare base documentation
- Configure development tools
- Keep database setup for the next branch
