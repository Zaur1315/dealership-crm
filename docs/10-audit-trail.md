# Audit Trail

The Audit Trail module records system-level changes in the CRM.

It is separate from the Lead Activity Timeline:

- Lead Activity Timeline shows business activity inside a lead profile.
- Audit Trail shows system changes across CRM entities.

## Storage

Audit logs are stored in the `audit_logs` table.

Each log stores:

- user
- user snapshot name
- action
- entity type
- entity ID
- entity label
- old values
- new values
- IP address
- user agent
- created timestamp

## Actions

Supported actions:

- Created
- Updated
- Deleted

## Audited Entities

Currently audited entities:

- Users
- Dealerships
- Leads
- Tasks

## Ignored Fields

The observer ignores technical/sensitive fields:

- `updated_at`
- `password`
- `remember_token`

## Admin UI

Audit Trail is available in the admin panel for GM users only.

Current filters:

- Action
- Entity
- User
- Date range

The detail page displays full old/new values.
