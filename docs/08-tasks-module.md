# Tasks Module

## Overview

Tasks belong to the currently selected dealership.

Tasks are not assigned to individual salespeople in version 1.

The Tasks tab displays all dealership tasks.

## Task Views

- Due Today
- Active
- Expired
- Completed

## Task Types

- Phone Call
- Email
- General

## Task Fields

- Title
- Description
- Type
- Due date and time
- Status
- Linked lead, optional
- Created by
- Completed by, optional
- Completed at, optional
- Verified email, optional

## Task Actions

Users can:

- View tasks
- Create manual tasks
- Mark tasks as complete
- Open linked lead profile

## Email Task Verification

Email-type tasks cannot be completed unless the CRM has a recorded outgoing email to the linked lead email address.

When completed, the task must store a reference to the email that satisfied the requirement.

Recommended field:

```text
verified_email_id
```

## Call Task Verification

Call tasks are manually confirmed in version 1.

Twilio integration may add automatic call verification in a future version.

## Expired Tasks

A task becomes expired when:

- Due date has passed
- Task is not completed

Task expiration must trigger notifications according to the notification rules.
