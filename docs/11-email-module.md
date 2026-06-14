# Email Module

## Overview

The CRM includes a full email client.

Each dealership has one shared inbox, for example:

```text
sales@dealershipname.com
```

All users assigned to the dealership share the same inbox.

## Provider Strategy

Do not build a custom email server.

Use a trusted provider such as Google Workspace.

Integration:

- IMAP for receiving emails
- SMTP for sending emails

## Credentials

Email credentials must not be hardcoded.

Recommended production approach:

- Store credentials per dealership.
- Encrypt sensitive fields.
- Keep `.env` and server secrets out of Git.

## Email Views

- Inbox
- Sent
- Trash
- Hidden Emails

Hidden Emails are visible only to GM and Managers.

## Email Actions

Users can:

- Compose email
- Reply to email
- Receive emails
- Send emails
- Attach files
- Link email to lead manually

## Lead Email Linking

Emails are matched to leads by customer email address.

Incoming and outgoing emails can be linked to a lead.

Unmatched website inquiries should be flagged for manual review.

No automatic lead creation in version 1.

## Email Deletion Model

Emails are never truly deleted except by GM.

Recommended statuses:

- `active`
- `trashed`
- `hidden`
- `deleted`

Deletion flow:

```text
active -> trashed -> hidden -> deleted
```

Salesperson and Manager deletion:

- Moves email to trash.
- After 30 days, email becomes hidden.
- Hidden emails are still visible to GM and Managers.

GM permanent deletion:

- Truly deletes or marks as permanently deleted, depending on implementation.

## Invoice Alert

Invoice alert is triggered when both conditions are true:

- Outgoing email has attachment.
- Subject or body contains the word `invoice`.

When triggered, notify Manager and GM.

Notification should include:

- Salesperson name
- Email subject
- Full email body
- Attachment indicator
- Link to email
