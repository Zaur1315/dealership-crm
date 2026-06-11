# Project Overview

## Purpose

Dealership CRM is a custom CRM system for a multi-dealership automotive group operating across the United States.

The system is intended for:

- Owner / General Manager
- Managers
- Salespeople

## Main Business Goals

The CRM must provide:

- Multi-dealership management under one platform
- Role-based access control
- Lead management with pipeline tracking
- Automated task generation
- Shared dealership email inboxes
- Notifications and reminders
- Statistics and reporting
- Secure authentication
- Login audit trail

## Technical Stack

The selected stack prioritizes development speed, stability, and fast production deployment.

* Laravel 13
* PHP 8.3+
* PostgreSQL
* Filament 5
* Livewire
* Blade
* Vue/Inertia where needed
* Queue workers
* Laravel Scheduler

## Frontend Strategy

Filament will be used for fast CRUD/admin screens.

Blade and Alpine.js will be used where lightweight interactivity is enough.

Vue/Inertia may be used only for complex screens, such as:

- Leads kanban board
- Email client
- Statistics charts
- Advanced notification UI

## Main Modules

- Authentication
- User Management
- Dealership Management
- Leads
- Tasks
- Email
- Notifications
- Statistics
- Settings

## Development Strategy

Development is divided into feature branches.

Each feature branch must include:

- Working code
- Migrations, if needed
- Tests, where practical
- Updated documentation
- Summary in changelog

## Admin Navigation

The CRM admin panel contains the following navigation groups:

### CRM

- Leads
- Tasks
- Email

### Reports

- Statistics

### Administration

- User Management
- Dealerships
- Settings

Access to navigation items is role-based:

- GM can access all sections.
- Manager can access CRM and Statistics.
- Salesperson can access CRM only.
