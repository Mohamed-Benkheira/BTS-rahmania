<?php

use App\Http\Controllers\Portal\AssignmentsController;
use App\Http\Controllers\Portal\AvailabilityController;
use App\Http\Controllers\Portal\CertificationsController;
use App\Http\Controllers\Portal\DashboardController;
use App\Http\Controllers\Portal\EvaluationsController;
use App\Http\Controllers\Portal\LanguagesController;
use App\Http\Controllers\Portal\NotificationsController;
use App\Http\Controllers\Portal\ProfileController;
use App\Http\Controllers\Portal\RequestsController;
use App\Http\Controllers\Portal\SkillsController;
use App\Http\Middleware\EnsureEmployeeProfile;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', EnsureEmployeeProfile::class])->prefix('portal')->group(function () {
    Route::get('/', DashboardController::class)->name('portal.dashboard');
    Route::get('/profile', [ProfileController::class, 'show'])->name('portal.profile');
    Route::post('/profile', [ProfileController::class, 'store'])->name('portal.profile.store');

    Route::get('/skills', [SkillsController::class, 'index'])->name('portal.skills.index');
    Route::post('/skills', [SkillsController::class, 'store'])->name('portal.skills.store');

    Route::get('/languages', [LanguagesController::class, 'index'])->name('portal.languages.index');
    Route::post('/languages', [LanguagesController::class, 'store'])->name('portal.languages.store');

    Route::get('/certifications', [CertificationsController::class, 'index'])->name('portal.certifications.index');
    Route::post('/certifications', [CertificationsController::class, 'store'])->name('portal.certifications.store');

    Route::get('/availability', [AvailabilityController::class, 'index'])->name('portal.availability.index');
    Route::post('/availability', [AvailabilityController::class, 'store'])->name('portal.availability.store');

    Route::get('/my-requests', [RequestsController::class, 'index'])->name('portal.requests.index');
    Route::get('/my-assignments', [AssignmentsController::class, 'index'])->name('portal.assignments.index');
    Route::get('/my-evaluations', [EvaluationsController::class, 'index'])->name('portal.evaluations.index');

    Route::get('/notifications', [NotificationsController::class, 'index'])->name('portal.notifications.index');
    Route::get('/notifications/unread-count', [NotificationsController::class, 'unreadCount'])->name('portal.notifications.unread-count');
    Route::get('/notifications/recent', [NotificationsController::class, 'recent'])->name('portal.notifications.recent');
    Route::post('/notifications/read-all', [NotificationsController::class, 'markAllRead'])->name('portal.notifications.read-all');
});
