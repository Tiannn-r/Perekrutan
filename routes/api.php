<?php

use App\Http\Controllers\Api\CandidateController;
use App\Http\Controllers\Api\InterviewController;
use App\Http\Controllers\Api\PortalController;
use App\Http\Controllers\Api\TemplateController;
use App\Http\Controllers\Api\WebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for RecruitFlow
|--------------------------------------------------------------------------
*/

// 1. Ingestion Webhook for Google Form
Route::post('/webhooks/google-form', [WebhookController::class, 'handleGoogleForm']);

// 2. HR Dashboard Endpoints
Route::get('/candidates', [CandidateController::class, 'index']);
Route::get('/dashboard/metrics', [CandidateController::class, 'metrics']);
Route::post('/interviews/schedule', [InterviewController::class, 'schedule']);
Route::post('/interviews/{id}/reschedule-resolve', [InterviewController::class, 'resolveReschedule']);
Route::get('/interviews/{id}/reminders', [InterviewController::class, 'getReminders']);
Route::post('/interviews/{id}/reminder', [InterviewController::class, 'sendReminder']);

// 3. Message Template Management Endpoints (Admin Panel)
Route::get('/templates', [TemplateController::class, 'index']);
Route::get('/templates/{key}', [TemplateController::class, 'show']);
Route::put('/templates/{key}', [TemplateController::class, 'update']);
Route::post('/templates/{key}/reset', [TemplateController::class, 'reset']);
Route::post('/templates/{key}/preview', [TemplateController::class, 'preview']);

// 4. Candidate Response Portal Endpoints
Route::get('/portal/{token}', [PortalController::class, 'show']);
Route::post('/portal/{token}/respond', [PortalController::class, 'respond']);
