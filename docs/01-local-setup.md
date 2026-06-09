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

## PostgreSQL

Default local database settings:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=dealership_crm
DB_USERNAME=dealership_crm_user
DB_PASSWORD=dealership_crm_password
```

### Create local database:

```sql
CREATE USER dealership_crm_user WITH PASSWORD 'dealership_crm_password';
CREATE DATABASE dealership_crm OWNER dealership_crm_user;
GRANT ALL PRIVILEGES ON DATABASE dealership_crm TO dealership_crm_user;
```


Run migrations:

```php
php artisan migrate
```
