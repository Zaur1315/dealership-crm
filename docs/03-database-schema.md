# Database Schema

## Database Engine

The project uses PostgreSQL.

## Core Tables

### users

Stores CRM users.

Important fields:

- `full_name`
- `username`
- `password`
- `role`
- `status`
- `telegram_contact`
- `failed_login_attempts`
- `locked_at`
- `last_login_at`
- `deleted_at`

Roles:

- `gm`
- `manager`
- `salesperson`

Statuses:

- `active`
- `locked`
- `deactivated`

### dealerships

Stores dealership records.

Important fields:

- `name`
- `email`
- `is_active`
- `deleted_at`

Dealerships are soft-deleted. When a dealership is removed, its related business data should become inaccessible but remain recoverable.

### dealership_user

Pivot table for the many-to-many relation between users and dealerships.

A user can belong to multiple dealerships.

A dealership can have multiple users.

### login_audits

Stores successful login events.

Important fields:

- `user_id`
- `ip_address`
- `user_agent`
- `logged_in_at`

The login audit trail is visible only to GM users.
