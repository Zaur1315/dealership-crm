# User Management

## Access

User Management is available only to GM.

## User List

Users should be displayed grouped by dealership.

Rules:

- Each dealership is a collapsible section.
- Users assigned to multiple dealerships appear under each relevant dealership.
- Deactivated users remain visible but should be visually distinct.

## User Creation Fields

GM can create users with:

- Full name
- Username
- Password
- Role
- Assigned dealership(s)
- Telegram contact

## User Profile

User profile should show:

- Full name
- Username
- Role
- Assigned dealership(s)
- Account status
- Telegram contact
- Login audit trail

## User Actions

GM can:

- Edit user details
- Reset password
- Lock account
- Unlock account
- Add dealership assignment
- Remove dealership assignment
- Deactivate account
- Reactivate account
- Delete account permanently

## Unlock Rule

Unlocking a locked account requires setting a new password at the same time.

## Deleted Users

When a user is permanently deleted:

- User account is removed.
- Comments, tasks, and activity remain intact.
- Historical display name should be preserved.

Recommended display format:

```text
[Name] - Deleted User
```

## Implemented Features

- GM-only User Management resource.
- User list with role, status, assigned dealerships, last login and created date.
- User creation.
- User editing.
- User profile view.
- Dealership assignment through many-to-many relationship.
- Password reset.
- Lock and unlock.
- Deactivate and reactivate.
- Permanent delete.

## Login Audit Trail

The user profile page displays a read-only login audit trail.

Visible fields:

- Logged in at
- IP address
- User agent

Only GM users can access user profiles and view login audit trails.

## Safety Rules

The current authenticated user cannot delete, lock, or deactivate their own account from the User Management table.
