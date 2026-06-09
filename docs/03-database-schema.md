# Database Schema

## Status

Database implementation will start in the `feature/database-foundation` branch.

## Database Engine

The project uses PostgreSQL.

## Initial Tables

The first database foundation stage should include:

- `users`
- `dealerships`
- `dealership_user`
- `login_audits`

## Future Tables

Planned tables:

- `leads`
- `lead_comments`
- `tasks`
- `emails`
- `email_attachments`
- `notifications`
- `dealership_email_settings`

## General Rules

- Use PostgreSQL-compatible migrations.
- Use meaningful table and column names.
- Use foreign keys where data integrity is important.
- Use indexes for frequently filtered fields.
- Use soft deletes where business history must be preserved.
- Do not hard-delete dealership data by default.

## Soft Delete Strategy

Recommended soft delete usage:

- `dealerships`
- `users`
- `leads`
- `emails`, depending on deletion status model

When a dealership is deleted, related data should become inaccessible but remain preserved for potential recovery.

## Email Storage Strategy

Emails should not be permanently deleted except by explicit GM action.

Recommended email status values:

- `active`
- `trashed`
- `hidden`
- `deleted`

## User History Strategy

When user-generated content is created, store enough author snapshot data to preserve display history even if the user is later deleted.
