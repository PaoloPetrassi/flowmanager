<?php

use App\Http\Controllers\Api\V1\InboundTicketController;
use App\Http\Controllers\Api\V1\ResourceController;
use App\Http\Controllers\ApiDocumentationController;
use App\Http\Middleware\AuthenticateApiToken;
use Illuminate\Support\Facades\Route;

Route::get('/openapi.json', [ApiDocumentationController::class, 'specification'])
    ->name('api.openapi');

Route::prefix('v1')
    ->middleware(AuthenticateApiToken::class)
    ->group(function () {
        Route::post('/tickets/inbound', InboundTicketController::class);

        Route::get('/{resource}', [ResourceController::class, 'index'])
            ->whereIn('resource', ['companies', 'contacts', 'projects', 'tasks', 'tickets', 'assets']);

        Route::get('/{resource}/{id}', [ResourceController::class, 'show'])
            ->whereIn('resource', ['companies', 'contacts', 'projects', 'tasks', 'tickets', 'assets'])
            ->whereNumber('id');
    });
