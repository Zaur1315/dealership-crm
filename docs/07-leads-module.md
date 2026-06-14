## Lead Pipeline

Leads use a fixed pipeline defined by `App\Enums\LeadPipelineStage`.

Available stages:

- New
- In Communication
- Did Not Answer
- In Negotiation
- Contract
- Invoice
- Won
- Lost
- Not Interested

The Leads list has pipeline tabs with counters. Counters are scoped to the currently selected dealership.

### Stage Management

Lead stage can be changed from:

- Lead edit form
- Leads table action `Change Stage`
- Quick table actions:
    - `Mark Won`
    - `Mark Lost`
    - `Not Interested`

Stage changes are handled by `App\Services\Leads\LeadPipelineService`.

The service is responsible for:

- updating `pipeline_stage`
- setting stage timestamps
- triggering Did Not Answer follow-up task automation

### Stage Timestamps

The following timestamps are set automatically once:

- `first_communication_at` when moved to `In Communication`
- `won_at` when moved to `Won`
- `lost_at` when moved to `Lost`
- `not_interested_at` when moved to `Not Interested`

### Did Not Answer Automation

When a lead is moved to `Did Not Answer`, the CRM creates a six-step follow-up task sequence.

The sequence is created only once per lead to avoid duplicates.

### Assigned Salesperson

Leads have an optional `assigned_to_user_id`.

The Leads list includes:

- `Assigned To` column
- `Assigned To` filter
- `Assign` table action

When a lead is created, it is automatically assigned to the current user.

## Lead Activity Timeline

Each lead profile includes an Activity Timeline.

The timeline is stored in the `lead_activities` table and displayed on the lead view page through `ActivitiesRelationManager`.

Activity records are created through `App\Services\Leads\LeadActivityService`.

### Supported Activity Types

- Lead Created
- Comment Added
- Task Created
- Task Updated
- Task Completed
- Task Expired
- Stage Changed
- Assigned User Changed

### Activity Data

Each activity stores:

- dealership
- lead
- user
- user snapshot name
- activity type
- title
- description
- related subject type and ID
- old values
- new values
- created timestamp

### Current Timeline Sources

The CRM currently writes timeline events when:

- a lead is created
- a lead stage is changed
- assigned salesperson is changed
- a comment is added
- a task is created
- a task is updated
- a task is completed
- a task expires automatically

### Future Timeline Sources

The timeline is prepared for future integrations:

- emails
- calls
- audit log events
- invoice events
