# Notifications

## Foundation

CRM notifications are stored in the `crm_notifications` table.

Each notification belongs to a recipient user and may optionally belong to a dealership.

Implemented fields:

- dealership
- recipient user
- type
- title
- body
- target type
- target ID
- target URL
- payload
- read timestamp
- expiration timestamp

## Retention

Notifications expire after 7 days.

Expired notifications can be removed with:

```bash
php artisan app:crm-notifications:prune-expired
```

## Implemented

- CRM notifications are stored in `crm_notifications`.
- Notifications are scoped by recipient user.
- Notifications may be linked to dealership.
- New lead notification is created automatically.
- Expired task notification is created automatically.
- Notifications expire after 7 days.
- Users can view unread/read/all notifications.
- Users can mark one notification as read.
- Users can mark all notifications as read.
- Dashboard widget shows latest unread notifications.
- Opening a notification marks it as read.

## Scheduler

The following scheduled commands are used:

- `app:tasks:expire-overdue` — runs every five minutes.
- `app:crm-notifications:prune-expired` — runs daily.

Production server must run Laravel scheduler:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
