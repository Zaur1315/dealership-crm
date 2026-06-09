# Auth and Roles

## Login Method

The CRM uses:

- Username
- Password

Email-based login is not used.

Password recovery flow is not part of version 1.

## Roles

The system has three roles:

- GM
- Manager
- Salesperson

## Role Access Summary

### GM

GM has full access to:

- All dealerships
- User management
- Dealership management
- Settings
- Leads
- Tasks
- Email
- Statistics
- Login audit trail

### Manager

Manager has access only to assigned dealerships.

Manager can:

- Create leads
- Edit leads
- Delete leads
- Use tasks
- Use email
- View statistics
- View hidden emails

Manager cannot:

- Manage users
- Manage dealerships
- Access global settings
- Unlock user accounts

### Salesperson

Salesperson has access only to assigned dealerships.

Salesperson can:

- Create leads
- Edit leads
- Move leads through pipeline
- Add comments
- Use tasks
- Use email

Salesperson cannot:

- Delete leads
- View statistics
- Manage users
- Manage dealerships
- View hidden emails
- Permanently delete emails

## Password Policy

Passwords must meet the following requirements:

- Minimum 8 characters
- At least one number
- At least one special character

## Account Statuses

User account statuses:

- `active`
- `locked`
- `deactivated`

## Failed Login Lockout

After 10 consecutive failed login attempts, the account is locked.

Only GM can unlock a locked account.

Unlocking must require setting a new password.

## Login Audit Trail

Every successful login must be logged with:

- User ID
- Timestamp
- IP address
- User agent, if useful

Only GM can view login audit trails.
