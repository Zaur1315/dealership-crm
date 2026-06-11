# Dealership Context

## Purpose

The CRM is scoped by dealership.

Every core module must show data only for the currently selected dealership.

This includes:

- Leads
- Tasks
- Emails
- Notifications
- Statistics

## Access Model

Users and dealerships have a many-to-many relationship.

A user can belong to multiple dealerships.

Access is additive:

- Adding a dealership gives access to that dealership.
- Removing a dealership revokes access to that dealership.
- GM always has access to all dealerships.

## Current Dealership

The application must maintain the currently selected dealership for the logged-in user.

Recommended service:

```php
CurrentDealershipContext
```

Recommended responsibilities:

```text
getCurrent()
setCurrent()
availableForUser()
assertAccess()
```

## Dealership Switcher

The UI must include a dealership switcher in the top-left corner.

Behavior:

- Switching dealership reloads the current dashboard/view.
- Every view becomes scoped to the selected dealership.
- No cross-dealership data mixing is allowed.

## Login Behavior

Manager:

- Must select a dealership before proceeding if assigned to multiple dealerships.

Salesperson:

- If assigned to one dealership, goes directly to dashboard.
- If assigned to multiple dealerships, selects dealership.

GM:

- Can select from all dealerships.

## Security Rule

Never rely only on frontend filtering.

Every backend query must be dealership-scoped.

## Dealership Selection Middleware

The admin panel uses `EnsureDealershipSelected` middleware.

It redirects authenticated users to `/admin/select-dealership` when no active dealership is selected in the current session.

Excluded paths:

- `/admin/login`
- `/admin/logout`
- `/admin/select-dealership`
