# Notifications

## UI

The CRM header must include a notification bell.

The bell must show unread count.

Clicking the bell opens a dropdown panel.

## Notification Behavior

Each notification shows:

- Type
- Short description
- Timestamp

Users can:

- Mark individual notification as read
- Mark all notifications as read
- Click notification to open related entity

Notifications should auto-clear after 7 days.

## Notification Recipients

Recommended implementation:

Create one notification row per recipient user.

This makes unread counting and read tracking simple.

## Notification Types

Planned notification types:

- New lead created
- Task due
- Task expired after one-hour grace period
- User logs in with overdue tasks
- Invoice alert
- New email arrived

## Suggested Table Fields

- `id`
- `recipient_user_id`
- `dealership_id`
- `type`
- `title`
- `body`
- `target_type`
- `target_id`
- `payload`
- `read_at`
- `created_at`
- `expires_at`

## Recipient Rules

New lead:

- All salespeople and managers in dealership

Task due:

- All salespeople and managers in dealership

Task expired after grace period:

- Manager and GM

User logs in with overdue tasks:

- That user only

Invoice alert:

- Manager and GM

New email:

- All salespeople and managers in dealership
