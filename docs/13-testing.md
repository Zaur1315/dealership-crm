# Testing

## Testing Strategy

The project should include practical tests for core business flows.

## Test Types

Recommended test types:

- Feature tests for user-facing flows
- Policy tests for role access
- Unit tests for services
- Smoke tests for critical pages

## Minimum Checks Per Branch

Each feature branch should pass:

```bash
composer pint
composer analyse
composer test
```

Or:

```bash
composer check
```

## Priority Test Areas

High-priority areas:

- Login
- Account lockout
- Role-based access
- Dealership scoping
- User management
- Lead creation
- Pipeline stage changes
- Auto task creation
- Task completion
- Email task verification
- Notification generation

## Policy Tests

Policy tests are required for:

- GM-only actions
- Manager actions
- Salesperson restrictions
- Dealership access checks

## Database Tests

Use database refresh strategy appropriate for Laravel and PostgreSQL.

Avoid test logic that depends on local-only data.
