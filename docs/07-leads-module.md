# Leads Module

## Overview

Leads belong to a dealership, not to an individual user.

All salespeople and managers assigned to the dealership can view and work with the same leads.

Leads are created manually in version 1.

## Lead Fields

- Full name
- Phone number
- Email address
- Address
- Deal value
- Pipeline stage
- Comments
- Tasks
- Email thread link
- Call log placeholder

## Pipeline Stages

Pipeline stages are fixed and not configurable by users.

Stages:

1. New
2. In Communication
3. Did Not Answer
4. In Negotiation
5. Contract
6. Invoice
7. Won
8. Lost
9. Not Interested

## Actions

All roles can:

- Create leads
- Edit leads
- Move leads between stages
- Add comments

Only Manager and GM can:

- Delete leads permanently

## Lead Comments

Comments must store:

- Lead ID
- Author user ID
- Author display name snapshot
- Comment body
- Timestamp

Comments are never deleted as part of normal user deletion.

## Auto Task on Lead Creation

When a new lead is created, the system creates a task:

```text
Contact this lead
```

The task is dealership-wide.

## Did Not Answer Sequence

When a lead is moved to `Did Not Answer`, the system creates six follow-up tasks:

1. Phone Call — Day 1
2. Email — Day 1 later in day
3. Phone Call — Day 2
4. Email — Day 2 later in day
5. Phone Call — Day 3 or 4
6. Email — Day 3 or 4 later in day

The system must prevent duplicate sequence creation if the lead is moved repeatedly into the same stage.

## UI

Kanban board is recommended for pipeline visibility.

A list view may also be added if needed.
