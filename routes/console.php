<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('app:tasks:expire-overdue')
    ->everyFiveMinutes();

Schedule::command('app:crm-notifications:prune-expired')
    ->daily();

Schedule::command('app:tasks:notify-due')
    ->everyFiveMinutes();

Schedule::command('app:emails:sync-inbox')
    ->everyFiveMinutes();

Schedule::command('app:emails:hide-expired-trash')
    ->daily();
