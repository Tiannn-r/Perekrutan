<?php

use App\Http\Controllers\Api\CandidateController;
use App\Http\Controllers\Api\InterviewController;
use App\Http\Controllers\Api\PortalController;
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
Route::post('/interviews/{id}/reminder', [InterviewController::class, 'sendReminder']);

// 3. Candidate Response Portal Endpoints
Route::get('/portal/{token}', [PortalController::class, 'show']);
Route::post('/portal/{token}/respond', [PortalController::class, 'respond']);
