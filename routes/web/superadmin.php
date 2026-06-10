<?php

use App\Http\Controllers\SuperAdmin\BlogController;
use App\Http\Controllers\SuperAdmin\CmsPageController;
use App\Http\Controllers\SuperAdmin\InquiryController;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\SuperAdmin\PlatformAnalyticsController;
use App\Http\Controllers\SuperAdmin\SocietyController;
use App\Http\Controllers\SuperAdmin\SubscriptionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Super Admin Platform Panel Routes
|--------------------------------------------------------------------------
| Mounted inside the ['auth', 'tenant', 'subscription'] group defined in
| routes/web.php. Everything here is additionally guarded by the
| role:super-admin middleware so non-super-admins receive a 403.
*/

Route::middleware('role:super-admin')->group(function () {

    // ---- Societies ----
    Route::get('societies', [SocietyController::class, 'index'])->name('societies.index');
    Route::get('societies/create', [SocietyController::class, 'create'])->name('societies.create');
    Route::post('societies', [SocietyController::class, 'store'])->name('societies.store');
    Route::get('societies/{society}', [SocietyController::class, 'show'])->name('societies.show');
    Route::get('societies/{society}/edit', [SocietyController::class, 'edit'])->name('societies.edit');
    Route::put('societies/{society}', [SocietyController::class, 'update'])->name('societies.update');
    Route::delete('societies/{society}', [SocietyController::class, 'destroy'])->name('societies.destroy');
    Route::post('societies/{society}/suspend', [SocietyController::class, 'suspend'])->name('societies.suspend');
    Route::post('societies/{society}/impersonate', [SocietyController::class, 'impersonate'])->name('societies.impersonate');
    Route::post('stop-impersonating', [SocietyController::class, 'stopImpersonating'])->name('societies.stop-impersonating');

    // ---- Subscription Plans ----
    Route::resource('plans', PlanController::class)->parameters(['plans' => 'plan']);

    // ---- Subscriptions ----
    Route::get('subscriptions', [SubscriptionController::class, 'index'])->name('subscriptions.index');
    Route::get('subscriptions/{subscription}', [SubscriptionController::class, 'show'])->name('subscriptions.show');
    Route::post('subscriptions/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscriptions.cancel');

    // ---- CMS Pages ----
    Route::get('cms', [CmsPageController::class, 'index'])->name('cms.index');
    Route::get('cms/create', [CmsPageController::class, 'create'])->name('cms.create');
    Route::post('cms', [CmsPageController::class, 'store'])->name('cms.store');
    Route::get('cms/{cms_page}', [CmsPageController::class, 'show'])->name('cms.show');
    Route::get('cms/{cms_page}/edit', [CmsPageController::class, 'edit'])->name('cms.edit');
    Route::put('cms/{cms_page}', [CmsPageController::class, 'update'])->name('cms.update');
    Route::delete('cms/{cms_page}', [CmsPageController::class, 'destroy'])->name('cms.destroy');
    Route::post('cms/{cms_page}/publish', [CmsPageController::class, 'publish'])->name('cms.publish');

    // ---- Blog ----
    Route::get('blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('blog/create', [BlogController::class, 'create'])->name('blog.create');
    Route::post('blog', [BlogController::class, 'store'])->name('blog.store');
    Route::get('blog/{blog}', [BlogController::class, 'show'])->name('blog.show');
    Route::get('blog/{blog}/edit', [BlogController::class, 'edit'])->name('blog.edit');
    Route::put('blog/{blog}', [BlogController::class, 'update'])->name('blog.update');
    Route::delete('blog/{blog}', [BlogController::class, 'destroy'])->name('blog.destroy');
    Route::post('blog/{blog}/publish', [BlogController::class, 'publish'])->name('blog.publish');

    // ---- Contact Inquiries ----
    Route::get('inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
    Route::get('inquiries/{inquiry}', [InquiryController::class, 'show'])->name('inquiries.show');
    Route::post('inquiries/{inquiry}/status', [InquiryController::class, 'updateStatus'])->name('inquiries.status');
    Route::delete('inquiries/{inquiry}', [InquiryController::class, 'destroy'])->name('inquiries.destroy');

    // ---- Platform Analytics ----
    Route::get('platform-analytics', [PlatformAnalyticsController::class, 'index'])->name('platform-analytics.index');
});
