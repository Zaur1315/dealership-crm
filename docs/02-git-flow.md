# Git Flow

## Branches

The project uses the following branch strategy:

- `main` — stable production-ready branch
- `dev` — integration branch
- `feature/*` — feature branches created from `dev`

## Branch Tree

```text
main
└── dev
    ├── feature/project-bootstrap
    ├── feature/database-foundation
    ├── feature/auth-users-roles
    ├── feature/dealerships
    ├── feature/layout-navigation
    ├── feature/user-management
    ├── feature/leads
    ├── feature/tasks
    ├── feature/notifications
    ├── feature/email-foundation
    ├── feature/email-client
    ├── feature/statistics
    ├── feature/pdf-export
    ├── feature/production-deployment
    └── feature/docs-finalization
```

## Create Feature Branch

```bash
git checkout dev
git pull origin dev
git checkout -b feature/example-feature
```

## Complete Feature Branch

```bash
git status
git add .
git commit -m "Describe completed feature"
```

## Merge Feature Branch Into Dev

```bash
git checkout dev
git merge feature/example-feature
```

## Push Branches

```bash
git push origin dev
git push origin feature/example-feature
```

## Branch Naming

Use clear branch names:

```text
feature/project-bootstrap
feature/database-foundation
feature/auth-users-roles
feature/dealerships
feature/layout-navigation
feature/user-management
feature/leads
feature/tasks
feature/notifications
feature/email-foundation
feature/email-client
feature/statistics
feature/pdf-export
feature/production-deployment
feature/docs-finalization
```

## Definition of Done

A feature branch is complete only when:

- Code works locally
- Migrations run successfully, if added
- Tests pass, if added
- Code style check passes
- Static analysis passes, if configured
- Documentation is updated
- Changelog is updated
- Branch can be merged into `dev` without conflicts
