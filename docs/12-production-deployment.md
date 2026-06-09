# Production Deployment

## Status

Production deployment will be prepared in the `feature/production-deployment` branch.

## Production Requirements

- PHP 8.3+
- PostgreSQL
- Web server: Nginx or Apache
- Composer
- Node.js for asset build, if building on server
- Queue worker
- Scheduler
- HTTPS

## Environment

Production `.env` must not be committed.

Required areas:

- App key
- App URL
- Database credentials
- Mail provider settings
- Queue connection
- Filesystem settings
- Logging settings

## Deployment Checklist

- Pull latest `main`
- Install Composer dependencies
- Install/build frontend assets
- Configure `.env`
- Run migrations with `--force`
- Create storage link
- Start/restart queue worker
- Configure scheduler cron
- Clear and cache config
- Clear and cache routes/views where needed
- Verify Filament login
- Verify first GM user
- Verify email settings
- Verify logs

## Useful Commands

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Scheduler Cron

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Queue Worker

Queue worker configuration depends on the server stack.

For production, use Supervisor or systemd.
