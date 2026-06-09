# Local Setup

## Requirements

- PHP 8.3+
- Composer
- Node.js
- npm
- PostgreSQL
- Git

## Clone Repository

```bash
git clone <repository-url>
cd dealership-crm
```

## Install PHP Dependencies

```bash
composer install
```

## Install Frontend Dependencies

```bash
npm install
```

## Environment File

```bash
cp .env.example .env
```

## Generate Application Key

```bash
php artisan key:generate
```

## Start Laravel Development Server

```bash
php artisan serve
```

Application URL:

```text
http://127.0.0.1:8000
```

## Start Frontend Build

Run in a separate terminal:

```bash
npm run dev
```

## Database

PostgreSQL setup will be completed in the `feature/database-foundation` branch.

The database setup documentation will be maintained in:

```text
docs/03-database-schema.md
```

## Useful Commands

Run tests:

```bash
composer test
```

Run code style formatter:

```bash
composer pint
```

Run static analysis:

```bash
composer analyse
```

Run all checks:

```bash
composer check
```
