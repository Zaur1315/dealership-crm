<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Statistics Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 12px;
        }

        h1 {
            font-size: 22px;
            margin-bottom: 4px;
        }

        h2 {
            font-size: 16px;
            margin-top: 24px;
            margin-bottom: 8px;
        }

        .muted {
            color: #6b7280;
        }

        .cards {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .cards td {
            width: 20%;
            border: 1px solid #d1d5db;
            padding: 10px;
            vertical-align: top;
        }

        .label {
            color: #6b7280;
            font-size: 11px;
        }

        .value {
            font-size: 18px;
            font-weight: bold;
            margin-top: 6px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th,
        table.data td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: left;
        }

        table.data th {
            background: #f3f4f6;
        }
    </style>
</head>
<body>
@php
    $formatMinutes = function (?float $minutes): string {
        if ($minutes === null) {
            return 'N/A';
        }

        if ($minutes < 60) {
            return number_format($minutes, 1) . ' min';
        }

        return number_format($minutes / 60, 1) . ' hr';
    };
@endphp

<h1>Statistics Report</h1>
<div class="muted">
    {{ $dealership->name }} |
    {{ $dateFrom->toDateString() }} - {{ $dateUntil->toDateString() }}
</div>

<table class="cards">
    <tr>
        <td>
            <div class="label">Total Leads</div>
            <div class="value">{{ $summary['total_leads'] }}</div>
        </td>
        <td>
            <div class="label">Won Deals</div>
            <div class="value">{{ $summary['won_deals'] }}</div>
        </td>
        <td>
            <div class="label">Conversion</div>
            <div class="value">{{ $summary['conversion_rate'] }}%</div>
        </td>
        <td>
            <div class="label">Revenue</div>
            <div class="value">${{ number_format($summary['revenue'], 2) }}</div>
        </td>
        <td>
            <div class="label">Expired Tasks</div>
            <div class="value">{{ $summary['expired_tasks'] }}</div>
        </td>
    </tr>
</table>

<h2>Additional Metrics</h2>
<table class="data">
    <tbody>
    <tr>
        <th>Average Response Time</th>
        <td>{{ $formatMinutes($averageResponseTimeMinutes) }}</td>
    </tr>
    <tr>
        <th>Email Sent</th>
        <td>{{ $emailActivity['sent'] }}</td>
    </tr>
    <tr>
        <th>Email Received</th>
        <td>{{ $emailActivity['received'] }}</td>
    </tr>
    <tr>
        <th>Task Completion Rate</th>
        <td>{{ $taskCompletion['completion_rate'] }}%</td>
    </tr>
    <tr>
        <th>Task Expired Rate</th>
        <td>{{ $taskCompletion['expired_rate'] }}%</td>
    </tr>
    </tbody>
</table>

<h2>Pipeline Breakdown</h2>
<table class="data">
    <thead>
    <tr>
        <th>Stage</th>
        <th>Count</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($pipeline as $stage => $count)
        <tr>
            <td>{{ $stage }}</td>
            <td>{{ $count }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>Task Breakdown</h2>
<table class="data">
    <thead>
    <tr>
        <th>Status</th>
        <th>Count</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($tasks as $status => $count)
        <tr>
            <td>{{ $status }}</td>
            <td>{{ $count }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>Leads Over Time</h2>
<table class="data">
    <thead>
    <tr>
        <th>Date</th>
        <th>New</th>
        <th>Won</th>
        <th>Lost</th>
        <th>Not Interested</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($leadsOverTime as $row)
        <tr>
            <td>{{ $row['period'] }}</td>
            <td>{{ $row['new'] }}</td>
            <td>{{ $row['won'] }}</td>
            <td>{{ $row['lost'] }}</td>
            <td>{{ $row['not_interested'] }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<h2>Revenue Over Time</h2>
<table class="data">
    <thead>
    <tr>
        <th>Date</th>
        <th>Revenue</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($revenueOverTime as $row)
        <tr>
            <td>{{ $row['period'] }}</td>
            <td>${{ number_format($row['revenue'], 2) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</body>
</html>
