<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InstallationApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['auth:sanctum', 'throttle:api'])->get('/user', function (Request $request) {
    return $request->user();
});

// =====================================
// REMOTE INSTALLATION API
// =====================================

Route::middleware('auth:sanctum')->prefix('remote')->name('api.remote.')->group(function () {

    // Installation Info - Basis informatie over deze installatie
    Route::get('/installation/info', [InstallationApiController::class, 'info'])->name('installation.info');
    Route::get('/installation/health', [InstallationApiController::class, 'health'])->name('installation.health');

});

// API endpoints voor company gebruikers (existing)
Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    Route::get('/companies/{company}/users', [\App\Http\Controllers\CompanyController::class, 'getUsers'])->name('api.companies.users');
    
    // Project API endpoints
    Route::get('/projects/{project}/milestones', [\App\Http\Controllers\ProjectController::class, 'getMilestones'])->name('api.projects.milestones');
    Route::get('/milestones/{milestone}/tasks', [\App\Http\Controllers\ProjectMilestoneController::class, 'getTasks'])->name('api.milestones.tasks');
    Route::get('/tasks/{task}/subtasks', [\App\Http\Controllers\ProjectTaskController::class, 'getSubtasks'])->name('api.tasks.subtasks');

    // Time entry work items endpoint
    Route::get('/projects/{project}/work-items', [\App\Http\Controllers\Api\ProjectController::class, 'getWorkItems'])->name('api.projects.work-items');
});

// Web-based API routes (for AJAX calls from authenticated pages)
Route::middleware('web')->group(function () {
    Route::get('/projects/{project}/work-items', [\App\Http\Controllers\Api\ProjectController::class, 'getWorkItems'])->name('api.web.projects.work-items');
});