# Email Module

## Overview

The CRM email module provides a shared dealership inbox inside the admin panel.

The implementation uses:

- Titan Email for mailbox hosting
- Titan IMAP for inbound email sync
- Titan SMTP for outgoing email
- NameSilo for domain/DNS management

Resend is not used for dealership CRM email sending.

## Dealership Email Settings

GM users configure email settings per dealership in Settings > Dealership Email.

Stored settings include:

- domain
- email address
- from email
- from name
- IMAP host, port, encryption, username, password
- SMTP host, port, encryption, username, password
- DNS, mailbox, and sending status
- active flag

Passwords are stored encrypted.

## Email Storage

Emails are stored in the `emails` table.

Attachments are stored in `email_attachments`.

Supported email directions:

- inbound
- outbound

Supported email statuses:

- active
- trashed
- hidden
- deleted

## Views

The Email module provides these views:

- Inbox
- Sent
- Trash
- Hidden
- Needs Review

Hidden emails are visible to GM and managers only.

## Inbound Sync

Inbound email is synced from Titan via:

```bash
php artisan app:emails:sync-inbox
```

The scheduler runs this command every five minutes.

Incoming emails are matched to leads by sender email address.

Unmatched emails are flagged with `needs_manual_review`.

## Manual Review

Users can:

- link an unmatched email to an existing lead
- create a new lead from an unmatched email

After linking or lead creation, the email is marked as matched and removed from manual review.

## Outgoing Email

Users can compose email from the CRM.

Outgoing email is sent through Titan SMTP and stored as an outbound email record.

Users can reply to inbound emails. Replies are linked to the same lead when available.

## Attachments

Outgoing attachments are uploaded and stored.

Incoming attachments are extracted during IMAP sync and stored.

Attachments can be downloaded from the Email view page.

## Layered Deletion

The CRM follows the layered deletion policy from the technical specification:

* Delete moves an email to Trash.
* Hide moves a trashed email to Hidden.
* Restore moves trashed/hidden email back to Active.
* GM can permanently mark hidden emails as Deleted.
* Trashed emails older than 30 days are automatically moved to Hidden.

Scheduled command:

```shell
php artisan app:emails:hide-expired-trash
```

## Notifications

The Email module creates notifications for:

* new inbound email
* invoice alerts

Invoice alert is triggered when an outgoing email contains an attachment and the word `invoice` appears in the subject or body.

## Task Verification

Email tasks can be completed only after an outgoing email exists for the linked lead.

The task stores `completed_with_email_id`.
