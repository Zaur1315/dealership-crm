# Changelog

## Unreleased

### Added

- Initial Laravel project documentation structure.
- Project overview documentation.
- Local setup documentation.
- Git flow documentation.
- Database schema placeholder documentation.
- Auth and roles planning documentation.
- Dealership context planning documentation.
- User management planning documentation.
- Leads module planning documentation.
- Tasks module planning documentation.
- Notifications planning documentation.
- Email module planning documentation.
- Statistics planning documentation.
- Production deployment planning documentation.
- Testing planning documentation.
- PostgreSQL database foundation.
- Core `users` table structure for CRM authentication.
- `dealerships` table.
- `dealership_user` pivot table.
- `login_audits` table.
- User role and status enums.
- Middleware that requires dealership selection before accessing CRM admin pages.
- CRM navigation placeholder pages.
- Role-based navigation visibility for Statistics, User Management, and Settings.
- Admin panel branding.
- Filament User Management resource.
- User creation and editing from admin panel.
- Dealership assignment on user form.
- Password reset action.
- User lock and unlock actions.
- User deactivate and reactivate actions.
- User profile view.

### Changed

- Project stack confirmed as Laravel 13 with Filament 5.
- Filament 3 installation attempt rejected because it is not compatible with the current Laravel 13 dependency tree.
