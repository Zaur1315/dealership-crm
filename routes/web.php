<?php

use App\Http\Controllers\CrmNotificationOpenController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/crm-notifications/{notification}/open', CrmNotificationOpenController::class)
    ->middleware(['web', 'auth'])
    ->name('crm-notifications.open');
