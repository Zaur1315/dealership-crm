# Statistics / Reports

## Implemented

The Statistics module provides dealership-scoped reporting for GM and Manager users.

Current report data includes:

- Total leads
- Won deals
- Conversion rate
- Revenue from won leads
- Expired tasks
- Pipeline breakdown
- Task breakdown

## Access

Statistics are available to:

- GM
- Manager

Salesperson users do not have access to the Statistics page.

## Dealership Scope

All statistics are calculated for the currently selected dealership.

If the user has access to only one dealership, the dealership context is selected automatically.

## Date Filters

The Statistics page supports date range filtering:

- Date From
- Date Until

The default range is the current month.

## PDF Export

The Statistics page supports PDF export for the selected date range.

Route:

```bash
/admin/statistics/export-pdf
```

The PDF includes:

* Dealership name
* Selected date range
* Summary metrics
* Pipeline breakdown
* Task breakdown
