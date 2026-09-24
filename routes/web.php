<?php

use App\Http\Controllers\Admin\ContentTypeController;
use App\Http\Controllers\ClientAccessController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicLinkController;
use App\Http\Controllers\PublicPedController;
use App\Http\Controllers\QuarterController;
use App\Http\Controllers\ShootingController;
use App\Http\Controllers\TopicPreviewController;
use App\Http\Controllers\TopicPreviewItemController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'welcome')->name('home');

Route::get('dashboard', DashboardController::class)
    ->middleware(['auth', 'verified', 'role:admin,account_manager,copywriter'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
});

Route::middleware(['auth', 'role:admin,account_manager,copywriter'])->group(function () {
    Route::resource('clients', ClientController::class)->except(['show']);

    Route::get('clients/{client}/quarters', [QuarterController::class, 'index'])->name('quarters.index');
    Route::post('clients/{client}/quarters', [QuarterController::class, 'store'])->name('quarters.store');
    Route::get('quarters/{quarter}', [QuarterController::class, 'show'])->name('quarters.show');
    Route::patch('quarters/{quarter}/status', [QuarterController::class, 'updateStatus'])->name('quarters.status');
    Route::get('quarters/{quarter}/feed', [QuarterController::class, 'feed'])->name('quarters.feed');

    Route::get('quarters/{quarter}/contents/create', [ContentController::class, 'create'])->name('contents.create');
    Route::post('quarters/{quarter}/contents', [ContentController::class, 'store'])->name('contents.store');
    Route::get('contents/{content}/edit', [ContentController::class, 'edit'])->name('contents.edit');
    Route::put('contents/{content}', [ContentController::class, 'update'])->name('contents.update');
    Route::delete('contents/{content}', [ContentController::class, 'destroy'])->name('contents.destroy');
    Route::patch('contents/{content}/status', [ContentController::class, 'updateStatus'])->name('contents.status');
    Route::post('contents/{content}/comments', [CommentController::class, 'store'])->name('comments.store');

    Route::get('quarters/{quarter}/topics', [TopicPreviewController::class, 'index'])->name('topics.index');
    Route::post('quarters/{quarter}/topics', [TopicPreviewController::class, 'store'])->name('topics.store');
    Route::put('topics/{topicPreview}', [TopicPreviewController::class, 'update'])->name('topics.update');
    Route::delete('topics/{topicPreview}', [TopicPreviewController::class, 'destroy'])->name('topics.destroy');

    Route::post('topics/{topicPreview}/items', [TopicPreviewItemController::class, 'store'])->name('topic-items.store');
    Route::put('topic-items/{item}', [TopicPreviewItemController::class, 'update'])->name('topic-items.update');
    Route::delete('topic-items/{item}', [TopicPreviewItemController::class, 'destroy'])->name('topic-items.destroy');

    // Trasforma un tema approvato in un contenuto reale.
    Route::post('topic-items/{item}/promote', [TopicPreviewItemController::class, 'promote'])->name('topic-items.promote');
});

Route::middleware(['auth', 'role:admin,account_manager'])->prefix('shooting')->group(function () {
    Route::get('/', [ShootingController::class, 'index'])->name('shooting.index');
    Route::post('targets', [ShootingController::class, 'storeTarget'])->name('shooting.targets.store');
    Route::post('sessions', [ShootingController::class, 'storeSession'])->name('shooting.sessions.store');
    Route::put('planning-rules', [ShootingController::class, 'updatePlanningRules'])->name('shooting.planning-rules.update');
});

Route::middleware(['auth', 'role:admin'])->prefix('settings')->group(function () {
    Route::resource('content-types', ContentTypeController::class)
        ->except(['show', 'create', 'edit'])
        ->names('settings.content-types');
    Route::put('artifact-prompt-rules', [ContentTypeController::class, 'updateArtifactPromptRules'])
        ->name('settings.artifact-prompt-rules.update');

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle');
});

Route::middleware(['auth', 'role:admin,account_manager'])->group(function () {
    Route::post('clients/{client}/public-link', [PublicLinkController::class, 'store'])->name('public-link.store');
    Route::delete('public-links/{link}', [PublicLinkController::class, 'destroy'])->name('public-link.destroy');

    Route::post('clients/{client}/access', [ClientAccessController::class, 'store'])->name('client-access.store');
    Route::delete('clients/{client}/access/{user}', [ClientAccessController::class, 'destroy'])->name('client-access.destroy');
    Route::post('clients/{client}/access/{user}/resend', [ClientAccessController::class, 'resend'])->name('client-access.resend');
});

Route::middleware('public-link')->prefix('ped/{clientSlug}')->group(function () {
    Route::get('/', [PublicPedController::class, 'entry'])->name('ped.entry');
    Route::post('check-email', [PublicPedController::class, 'checkEmail'])->name('ped.check-email');

    Route::middleware('auth')->group(function () {
        Route::get('argomenti', [PublicPedController::class, 'topics'])->name('ped.topics');
        Route::get('feed', [PublicPedController::class, 'feed'])->name('ped.feed');
        Route::get('shooting', [PublicPedController::class, 'shooting'])->name('ped.shooting');

        Route::post('contents/{content}/approve', [PublicPedController::class, 'approve'])->name('ped.approve');
        Route::post('contents/{content}/reject', [PublicPedController::class, 'reject'])->name('ped.reject');
        Route::post('contents/{content}/comment', [PublicPedController::class, 'comment'])->name('ped.comment');
        Route::post('topics/{topicPreview}/respond', [PublicPedController::class, 'respondToTopics'])->name('ped.topics.respond');
    });
});

require __DIR__.'/settings.php';
