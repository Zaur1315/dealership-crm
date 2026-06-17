<?php

use App\Http\Controllers\CrmNotificationOpenController;
use App\Http\Controllers\EmailAttachmentDownloadController;
use App\Http\Controllers\StatisticsPdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/admin/crm-notifications/{notification}/open', CrmNotificationOpenController::class)
    ->middleware(['web', 'auth'])
    ->name('crm-notifications.open');

Route::get('/admin/email-attachments/{attachment}/download', EmailAttachmentDownloadController::class)
    ->middleware(['web', 'auth'])
    ->name('email-attachments.download');

Route::get('/admin/statistics/export-pdf', StatisticsPdfController::class)
    ->middleware(['web', 'auth'])
    ->name('statistics.export-pdf');
