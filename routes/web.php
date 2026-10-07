<?php

use Illuminate\Support\Facades\Route;

use Illuminate\Support\Facades\Auth;

use Illuminate\Foundation\Auth\EmailVerificationRequest;

use Illuminate\Http\Request;

use App\Http\Controllers\PublicQuotationController;

use App\Http\Controllers\PublicQuotationAcceptanceController;



use App\Http\Controllers\Auth\LoginController;

use App\Http\Controllers\Auth\RegisterController;

use Illuminate\Support\Facades\Storage;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;

use App\Http\Controllers\Admin\ClientController;

use App\Http\Controllers\Admin\QuotationController as AdminQuotationController;

use App\Http\Controllers\Admin\GeneratedQuotationController as AdminGeneratedQuotationController;

use App\Http\Controllers\Admin\SupportController as AdminSupportController;

use App\Http\Controllers\Admin\AlertController as AdminAlertController;

use App\Http\Controllers\Admin\InspectorAvailabilityController as AdminInspectorAvailabilityController;

use App\Http\Controllers\Admin\JobOrderController as AdminJobOrderController;

use App\Http\Controllers\Admin\ReportController as AdminReportController;

use App\Http\Controllers\Admin\AuditLogController;

use App\Http\Controllers\Admin\WarrantyClaimController as AdminWarrantyClaimController;

use App\Http\Controllers\Admin\BackJobController as AdminBackJobController;

use App\Http\Controllers\Admin\ProfileController as AdminProfileController;



use App\Http\Controllers\Client\DashboardController as ClientDashboardController;

use App\Http\Controllers\Client\SupportController as ClientSupportController;

use App\Http\Controllers\Client\MyRequestController;

use App\Http\Controllers\Client\AlertController as ClientAlertController;

use App\Http\Controllers\Client\QuotationController as ClientQuotationController;

use App\Http\Controllers\Client\InvoiceController as ClientInvoiceController;

use App\Http\Controllers\Client\PaymentController as ClientPaymentController;

use App\Http\Controllers\Client\ContractController as ClientContractController;

use App\Http\Controllers\Client\ReceiptController as ClientReceiptController;

use App\Http\Controllers\Client\JobOrderController as ClientJobOrderController;

use App\Http\Controllers\Client\WarrantyClaimController as ClientWarrantyClaimController;

use App\Http\Controllers\Client\ProfileController as ClientProfileController;



use App\Http\Controllers\Hr\DashboardController as HrDashboardController;

use App\Http\Controllers\Hr\SupportController as HrSupportController;

use App\Http\Controllers\Hr\QuotationController as HrQuotationController;

use App\Http\Controllers\Hr\InvoiceController as HrInvoiceController;

use App\Http\Controllers\Hr\PaymentController as HrPaymentController;

use App\Http\Controllers\Hr\ContractController as HrContractController;

use App\Http\Controllers\Hr\ReceiptController as HrReceiptController;

use App\Http\Controllers\Hr\ReportController as HrReportController;

use App\Http\Controllers\Hr\AlertController as HrAlertController;

use App\Http\Controllers\Hr\InspectionReportController as HrInspectionReportController;

use App\Http\Controllers\Hr\ProfileController as HrProfileController;



use App\Http\Controllers\Inspector\DashboardController as InspectorDashboardController;

use App\Http\Controllers\Inspector\QuotationController as InspectorQuotationController;

use App\Http\Controllers\Inspector\AvailabilityController as InspectorAvailabilityController;

use App\Http\Controllers\Inspector\AlertController as InspectorAlertController;

use App\Http\Controllers\Inspector\ProfileController as InspectorProfileController;



/*

|--------------------------------------------------------------------------

| Public pages and quotation acceptance

|--------------------------------------------------------------------------

*/

Route::get('/', function () {

    return view('home');
})->name('home');



Route::view('/terms', 'terms')->name('terms');

Route::view('/privacy-policy', 'privacy-policy')->name('privacy-policy');



Route::get('/internal-portal', function () {

    return redirect()->route('login');
})->name('landing.internal');



Route::post('/free-quotation', [PublicQuotationController::class, 'store'])

    ->middleware('throttle:5,1')

    ->name('free-quotation.store');



Route::get('/quotation/view/{token}', [PublicQuotationAcceptanceController::class, 'show'])

    ->name('public.quotations.show');



Route::post('/quotation/view/{token}/accept', [PublicQuotationAcceptanceController::class, 'accept'])

    ->middleware('throttle:10,1')

    ->name('public.quotations.accept');



Route::post('/quotation/view/{token}/decline', [PublicQuotationAcceptanceController::class, 'decline'])

    ->middleware('throttle:10,1')

    ->name('public.quotations.decline');



/*

|--------------------------------------------------------------------------

| Unified authentication

|--------------------------------------------------------------------------

*/

Route::middleware('guest')->group(function () {

    Route::post('/register', [RegisterController::class, 'store'])

        ->middleware('throttle:5,1')

        ->name('register.store');



    Route::get('/login', [LoginController::class, 'show'])->name('login');

    Route::post('/login', [LoginController::class, 'authenticate'])

        ->middleware('throttle:5,1')

        ->name('login.attempt');
});



Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');



    Route::get('/admin/verify-otp', [LoginController::class, 'showAdminOtpForm'])

        ->name('admin.otp.form');



    Route::post('/admin/verify-otp', [LoginController::class, 'verifyAdminOtp'])

        ->middleware('throttle:5,1')

        ->name('admin.otp.verify');



    Route::post('/admin/verify-otp/resend', [LoginController::class, 'resendAdminOtp'])

        ->name('admin.otp.resend')

        ->middleware('throttle:3,1');
});



/*

|--------------------------------------------------------------------------

| Email verification

|--------------------------------------------------------------------------

*/

Route::get('/email/verify', function () {

    $user = Auth::user();



    if ($user && $user->email_verified_at !== null) {

        return match ($user->role) {

            'admin' => redirect()->route('admin.dashboard'),

            'hr' => redirect()->route('hr.dashboard'),

            'inspector' => redirect()->route('inspector.dashboard'),

            'client' => redirect()->route('client.dashboard'),

            default => redirect()->route('home'),
        };
    }



    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');



Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {

    $request->fulfill();



    $user = $request->user();



    return match ($user->role) {

        'admin' => redirect()->route('admin.dashboard')->with('success', 'Email verified successfully.'),

        'hr' => redirect()->route('hr.dashboard')->with('success', 'Email verified successfully.'),

        'inspector' => redirect()->route('inspector.dashboard')->with('success', 'Email verified successfully.'),

        'client' => redirect()->route('client.dashboard')->with('success', 'Email verified successfully. Welcome to WRPlumb.'),

        default => redirect()->route('home')->with('success', 'Email verified successfully.'),
    };
})->middleware(['auth', 'signed'])->name('verification.verify');



Route::post('/email/verification-notification', function (Request $request) {

    if ($request->user()->email_verified_at !== null) {

        return back()->with('success', 'Your email address is already verified.');
    }



    $request->user()->sendEmailVerificationNotification();



    return back()->with('success', 'Verification link sent again. Please check your Gmail inbox.');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');



/*

|--------------------------------------------------------------------------

| Client panel - email verification required

|--------------------------------------------------------------------------

*/

Route::middleware(['auth', 'verified', 'client'])->group(function () {

    Route::get('/client/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');



    Route::put('/client/profile', [ClientProfileController::class, 'update'])

        ->middleware('throttle:5,1')

        ->name('client.profile.update');



    Route::get('/client/support', [ClientSupportController::class, 'index'])

        ->name('client.support.index');



    Route::post('/client/support/send', [ClientSupportController::class, 'sendMessage'])

        ->middleware('throttle:10,1')

        ->name('client.support.send');



    Route::post('/client/support/clear', [ClientSupportController::class, 'clearChat'])

        ->middleware('throttle:5,1')

        ->name('client.support.clear');



    Route::get('/client/invoices', [ClientInvoiceController::class, 'index'])->name('client.invoices.index');

    Route::get('/client/invoices/{invoice}', [ClientInvoiceController::class, 'show'])->name('client.invoices.show');



    Route::get('/client/requests', [MyRequestController::class, 'index'])->name('client.requests.index');

    Route::get('/client/requests/create', [MyRequestController::class, 'create'])->name('client.requests.create');

    Route::post('/client/requests', [MyRequestController::class, 'store'])

        ->middleware('throttle:5,1')

        ->name('client.requests.store');

    Route::get('/client/availability/calendar', [MyRequestController::class, 'calendarAvailability'])->name('client.requests.calendar-availability');



    Route::get('/client/quotations', [ClientQuotationController::class, 'index'])->name('client.quotations.index');

    Route::get('/client/quotations/{quotation}', [ClientQuotationController::class, 'show'])->name('client.quotations.show');

    Route::post('/client/quotations/{quotation}/accept', [ClientQuotationController::class, 'accept'])

        ->middleware('throttle:10,1')

        ->name('client.quotations.accept');



    Route::get('/client/contracts', [ClientContractController::class, 'index'])->name('client.contracts.index');

    Route::get('/client/contracts/{contract}', [ClientContractController::class, 'show'])->name('client.contracts.show');

    Route::post('/client/contracts/{contract}/accept', [ClientContractController::class, 'accept'])

        ->middleware('throttle:10,1')

        ->name('client.contracts.accept');



    Route::get('/client/requests/{quotation}', [MyRequestController::class, 'show'])->name('client.requests.show');

    Route::post('/client/requests/{quotation}/request-reschedule', [MyRequestController::class, 'requestReschedule'])

        ->middleware('throttle:5,1')

        ->name('client.requests.request-reschedule');

    Route::post('/client/requests/{quotation}/request-cancel', [MyRequestController::class, 'requestCancel'])

        ->middleware('throttle:5,1')

        ->name('client.requests.request-cancel');



    Route::get('/client/payments', [ClientPaymentController::class, 'index'])->name('client.payments.index');

    Route::get('/client/payments/{payment}', [ClientPaymentController::class, 'show'])->name('client.payments.show');

    Route::get('/client/invoices/{invoice}/payments/create', [ClientPaymentController::class, 'create'])->name('client.payments.create');

    Route::post('/client/payments', [ClientPaymentController::class, 'store'])

        ->middleware('throttle:5,1')

        ->name('client.payments.store');



    Route::get('/client/receipts', [ClientReceiptController::class, 'index'])->name('client.receipts.index');

    Route::get('/client/receipts/{receipt}', [ClientReceiptController::class, 'show'])->name('client.receipts.show');



    Route::get('/client/job-orders', [ClientJobOrderController::class, 'index'])->name('client.job-orders.index');

    Route::get('/client/job-orders/{jobOrder}', [ClientJobOrderController::class, 'show'])->name('client.job-orders.show');



    Route::post('/client/job-orders/{jobOrder}/warranty-claims', [ClientWarrantyClaimController::class, 'store'])

        ->middleware('throttle:3,1')

        ->name('client.warranty-claims.store');



    Route::get('/client/alerts', [ClientAlertController::class, 'index'])

        ->name('client.alerts.index');



    Route::patch('/client/alerts/read-all', [ClientAlertController::class, 'markAllRead'])

        ->middleware('throttle:10,1')

        ->name('client.alerts.mark-all-read');



    Route::patch('/client/alerts/{alert}/read', [ClientAlertController::class, 'markRead'])

        ->middleware('throttle:30,1')

        ->name('client.alerts.mark-read');
});



/*

|--------------------------------------------------------------------------

| Legacy login URLs

|--------------------------------------------------------------------------

*/

Route::middleware('guest')->prefix('admin')->group(function () {

    Route::get('/login', fn() => redirect()->route('login'))->name('admin.login');

    Route::post('/login', fn() => redirect()->route('login'))->name('admin.login.send-otp');
});



Route::middleware('guest')->prefix('hr')->group(function () {

    Route::get('/login', fn() => redirect()->route('login'))->name('hr.login');

    Route::post('/login', fn() => redirect()->route('login'))->name('hr.login.attempt');
});



Route::middleware('guest')->prefix('inspector')->group(function () {

    Route::get('/login', fn() => redirect()->route('login'))->name('inspector.login');

    Route::post('/login', fn() => redirect()->route('login'))->name('inspector.login.attempt');
});



/*

|--------------------------------------------------------------------------

| Admin panel

|--------------------------------------------------------------------------

*/

Route::middleware(['auth', 'admin', 'admin.otp'])->prefix('admin')->group(function () {

    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');



    Route::get('/profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');

    Route::put('/profile', [AdminProfileController::class, 'update'])

        ->middleware('throttle:10,1')

        ->name('admin.profile.update');



    Route::get('/inspectors/availability', [AdminInspectorAvailabilityController::class, 'index'])->name('admin.inspectors.availability');

    Route::post('/inspectors/availability/day', [AdminInspectorAvailabilityController::class, 'storeOrUpdateDay'])

        ->middleware('throttle:20,1')

        ->name('admin.inspectors.availability.day');



    Route::get('/clients', [ClientController::class, 'index'])->name('admin.clients.index');

    Route::post('/clients', [ClientController::class, 'store'])->middleware('throttle:5,1')->name('admin.clients.store');

    Route::patch('/clients/{user}', [ClientController::class, 'update'])->middleware('throttle:10,1')->name('admin.clients.update');

    Route::patch('/clients/{user}/toggle-status', [ClientController::class, 'toggleStatus'])->middleware('throttle:10,1')->name('admin.clients.toggle-status');

    Route::patch('/clients/{user}/verify-email', [ClientController::class, 'verifyEmail'])->middleware('throttle:10,1')->name('admin.clients.verify-email');

    Route::patch('/clients/{user}/reset-password', [ClientController::class, 'resetPassword'])->middleware('throttle:5,1')->name('admin.clients.reset-password');



    Route::get('/quotations', [AdminQuotationController::class, 'index'])->name('admin.quotations.index');

    Route::post('/quotations/{quotation}/assign-worker', [AdminQuotationController::class, 'assignWorker'])->middleware('throttle:20,1')->name('admin.quotations.assign-worker');

    Route::post('/quotations/{quotation}/appointment', [AdminQuotationController::class, 'updateAppointment'])->middleware('throttle:20,1')->name('admin.quotations.update-appointment');

    Route::post('/quotations/{quotation}/client-request-review', [AdminQuotationController::class, 'reviewClientRequest'])->middleware('throttle:20,1')->name('admin.quotations.review-client-request');

    Route::patch('/quotations/{quotation}/flow', [AdminQuotationController::class, 'updateFlow'])->middleware('throttle:10,1')->name('admin.quotations.update-flow');

    Route::post('/quotations/{quotation}/send-to-hr', [AdminQuotationController::class, 'sendToHr'])->middleware('throttle:10,1')->name('admin.quotations.send-to-hr');



    Route::get('/quotations/archived', [AdminQuotationController::class, 'archived'])->name('admin.quotations.archived');

    Route::get('/archives', [AdminQuotationController::class, 'archived'])->name('admin.archives.index');

    Route::get('/archives/eligible-records', [AdminQuotationController::class, 'eligibleArchiveRecords'])->name('admin.archives.eligible-records');

    Route::post('/archives/bulk-archive', [AdminQuotationController::class, 'bulkArchiveRecords'])->middleware('throttle:10,1')->name('admin.archives.bulk-archive');

    Route::post('/archives/archive', [AdminQuotationController::class, 'archiveRecordManually'])->middleware('throttle:10,1')->name('admin.archives.archive');

    Route::patch('/archives/{type}/{id}/restore', [AdminQuotationController::class, 'restoreRecord'])->middleware('throttle:10,1')->name('admin.archives.restore');

    Route::patch('/quotations/{quotation}/restore', [AdminQuotationController::class, 'restore'])->middleware('throttle:10,1')->name('admin.quotations.restore');





    Route::get('/generated-quotations', [AdminGeneratedQuotationController::class, 'index'])

        ->name('admin.generated-quotations.index');



    Route::get('/generated-quotations/{quotation}', [AdminGeneratedQuotationController::class, 'show'])

        ->name('admin.generated-quotations.show');



    Route::get('/generated-quotations/{quotation}/edit', [AdminGeneratedQuotationController::class, 'edit'])

        ->name('admin.generated-quotations.edit');



    Route::put('/generated-quotations/{quotation}', [AdminGeneratedQuotationController::class, 'update'])

        ->middleware('throttle:10,1')

        ->name('admin.generated-quotations.update');



    Route::get('/support', [AdminSupportController::class, 'index'])->name('admin.support.index');

    Route::get('/support/{conversation}', [AdminSupportController::class, 'show'])->name('admin.support.show');

    Route::post('/support/{conversation}/reply', [AdminSupportController::class, 'reply'])->middleware('throttle:10,1')->name('admin.support.reply');

    Route::post('/support/{conversation}/resolve', [AdminSupportController::class, 'resolve'])->middleware('throttle:20,1')->name('admin.support.resolve');



    Route::get('/job-orders', [AdminJobOrderController::class, 'index'])->name('admin.job-orders.index');

    Route::get('/job-orders/create/{quotation}', [AdminJobOrderController::class, 'create'])->name('admin.job-orders.create');

    Route::post('/job-orders', [AdminJobOrderController::class, 'store'])->middleware('throttle:10,1')->name('admin.job-orders.store');

    Route::get('/job-orders/{jobOrder}', [AdminJobOrderController::class, 'show'])->name('admin.job-orders.show');

    Route::patch('/job-orders/{jobOrder}/start', [AdminJobOrderController::class, 'start'])->middleware('throttle:20,1')->name('admin.job-orders.start');

    Route::patch('/job-orders/{jobOrder}/complete', [AdminJobOrderController::class, 'complete'])->middleware('throttle:20,1')->name('admin.job-orders.complete');

    Route::patch('/job-orders/{jobOrder}/cancel', [AdminJobOrderController::class, 'cancel'])->middleware('throttle:10,1')->name('admin.job-orders.cancel');

    Route::patch('/job-orders/{jobOrder}/remarks', [AdminJobOrderController::class, 'updateRemarks'])->middleware('throttle:20,1')->name('admin.job-orders.update-remarks');

    Route::patch('/job-orders/{jobOrder}/reschedule', [AdminJobOrderController::class, 'reschedule'])->middleware('throttle:10,1')->name('admin.job-orders.reschedule');

    Route::patch('/job-orders/{jobOrder}/reassign', [AdminJobOrderController::class, 'reassign'])->middleware('throttle:10,1')->name('admin.job-orders.reassign');



    Route::get('/reports', [AdminReportController::class, 'index'])->name('admin.reports.index');

    Route::get('/reports/projects/export', [AdminReportController::class, 'exportProjects'])->name('admin.reports.projects.export');



    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('admin.audit-logs.index');



    Route::get('/warranty-claims', [AdminWarrantyClaimController::class, 'index'])->name('admin.warranty-claims.index');

    Route::get('/warranty-claims/{warrantyClaim}', [AdminWarrantyClaimController::class, 'show'])->name('admin.warranty-claims.show');

    Route::patch('/warranty-claims/{warrantyClaim}/approve', [AdminWarrantyClaimController::class, 'approve'])->middleware('throttle:10,1')->name('admin.warranty-claims.approve');

    Route::patch('/warranty-claims/{warrantyClaim}/reject', [AdminWarrantyClaimController::class, 'reject'])->middleware('throttle:10,1')->name('admin.warranty-claims.reject');

    Route::post('/warranty-claims/{warrantyClaim}/create-backjob', [AdminWarrantyClaimController::class, 'createBackJob'])->middleware('throttle:5,1')->name('admin.warranty-claims.create-backjob');



    Route::get('/backjobs', [AdminBackJobController::class, 'index'])->name('admin.backjobs.index');

    Route::get('/backjobs/{backJob}', [AdminBackJobController::class, 'show'])->name('admin.backjobs.show');

    Route::patch('/backjobs/{backJob}/schedule', [AdminBackJobController::class, 'schedule'])->middleware('throttle:10,1')->name('admin.backjobs.schedule');

    Route::patch('/backjobs/{backJob}/start', [AdminBackJobController::class, 'start'])->middleware('throttle:10,1')->name('admin.backjobs.start');

    Route::patch('/backjobs/{backJob}/resolve', [AdminBackJobController::class, 'resolve'])->middleware('throttle:10,1')->name('admin.backjobs.resolve');

    Route::patch('/backjobs/{backJob}/cancel', [AdminBackJobController::class, 'cancel'])->middleware('throttle:10,1')->name('admin.backjobs.cancel');



    Route::get('/alerts', [AdminAlertController::class, 'index'])->name('admin.alerts.index');

    Route::patch('/alerts/{alert}/read', [AdminAlertController::class, 'markRead'])->middleware('throttle:20,1')->name('admin.alerts.mark-read');

    Route::patch('/alerts/read-all', [AdminAlertController::class, 'markAllRead'])->middleware('throttle:10,1')->name('admin.alerts.mark-all-read');

    Route::delete('/alerts/clear-read', [AdminAlertController::class, 'clearRead'])->middleware('throttle:10,1')->name('admin.alerts.clear-read');



    Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');
});



/*

|--------------------------------------------------------------------------

| HR panel

|--------------------------------------------------------------------------

*/

Route::middleware(['auth', 'hr'])->prefix('hr')->group(function () {

    Route::get('/dashboard', [HrDashboardController::class, 'index'])->name('hr.dashboard');



    Route::put('/profile', [HrProfileController::class, 'update'])

        ->middleware('throttle:10,1')

        ->name('hr.profile.update');



    Route::get('/alerts', [HrAlertController::class, 'index'])

        ->name('hr.alerts.index');



    Route::patch('/alerts/read-all', [HrAlertController::class, 'markAllRead'])

        ->middleware('throttle:10,1')

        ->name('hr.alerts.mark-all-read');



    Route::patch('/alerts/{alert}/read', [HrAlertController::class, 'markRead'])

        ->middleware('throttle:30,1')

        ->name('hr.alerts.mark-read');



    Route::delete('/alerts/clear-read', [HrAlertController::class, 'clearRead'])

        ->middleware('throttle:10,1')

        ->name('hr.alerts.clear-read');





    Route::get('/inspection-reports', [HrInspectionReportController::class, 'index'])

        ->name('hr.inspection-reports.index');



    Route::get('/inspection-reports/{inspectionReport}', [HrInspectionReportController::class, 'show'])

        ->name('hr.inspection-reports.show');



    Route::patch('/inspection-reports/{inspectionReport}/review', [HrInspectionReportController::class, 'markReviewed'])

        ->middleware('throttle:10,1')

        ->name('hr.inspection-reports.review');



    Route::get('/quotations', [HrQuotationController::class, 'index'])

        ->name('hr.quotations.index');


    Route::post('/quotation-templates/import', [HrQuotationController::class, 'importTemplate'])

        ->middleware('throttle:10,1')

        ->name('hr.quotation-templates.import');



    Route::get('/quotations/archived', [HrQuotationController::class, 'archived'])

        ->name('hr.quotations.archived');



    Route::get('/quotations/create/{quotationRequest}', [HrQuotationController::class, 'create'])

        ->name('hr.quotations.create');



    Route::post('/quotations', [HrQuotationController::class, 'store'])

        ->middleware('throttle:10,1')

        ->name('hr.quotations.store');



    Route::patch('/quotations/{quotation}/archive', [HrQuotationController::class, 'archive'])

        ->middleware('throttle:10,1')

        ->name('hr.quotations.archive');



    Route::patch('/quotations/{quotation}/restore', [HrQuotationController::class, 'restore'])

        ->middleware('throttle:10,1')

        ->name('hr.quotations.restore');



    Route::post('/quotations/{quotation}/send', [HrQuotationController::class, 'send'])

        ->middleware('throttle:10,1')

        ->name('hr.quotations.send');



    Route::get('/quotations/{quotation}/document', [HrQuotationController::class, 'downloadGenerated'])

        ->name('hr.quotations.document');



    Route::get('/quotations/{quotation}', [HrQuotationController::class, 'show'])

        ->name('hr.quotations.show');



    Route::get('/invoices', [HrInvoiceController::class, 'index'])->name('hr.invoices.index');

    Route::get('/invoices/create/{quotation}', [HrInvoiceController::class, 'create'])->name('hr.invoices.create');

    Route::post('/invoices', [HrInvoiceController::class, 'store'])->middleware('throttle:10,1')->name('hr.invoices.store');

    Route::get('/invoices/{invoice}', [HrInvoiceController::class, 'show'])->name('hr.invoices.show');

    Route::get('/invoices/{invoice}/billing-statement', [HrInvoiceController::class, 'billingStatement'])->name('hr.invoices.billing-statement');

    Route::post(

        '/invoices/{invoice}/payment-schedules/{paymentSchedule}/mark-ready',

        [HrInvoiceController::class, 'markScheduleReady']

    )

        ->middleware('throttle:10,1')

        ->name('hr.invoices.payment-schedules.mark-ready');



    Route::get('/support', [HrSupportController::class, 'index'])->name('hr.support.index');

    Route::get('/support/{conversation}', [HrSupportController::class, 'show'])->name('hr.support.show');

    Route::post('/support/{conversation}/reply', [HrSupportController::class, 'reply'])->middleware('throttle:10,1')->name('hr.support.reply');

    Route::post('/support/{conversation}/escalate-admin', [HrSupportController::class, 'escalateToAdmin'])->middleware('throttle:10,1')->name('hr.support.escalate-admin');



    Route::get('/payments', [HrPaymentController::class, 'index'])->name('hr.payments.index');

    Route::get('/payments/create/{invoice}', [HrPaymentController::class, 'create'])->name('hr.payments.create');

    Route::post('/payments', [HrPaymentController::class, 'store'])->middleware('throttle:10,1')->name('hr.payments.store');

    Route::get('/payments/{payment}', [HrPaymentController::class, 'show'])->name('hr.payments.show');

    Route::post('/payments/{payment}/confirm', [HrPaymentController::class, 'confirm'])->middleware('throttle:10,1')->name('hr.payments.confirm');

    Route::post('/payments/{payment}/reject', [HrPaymentController::class, 'reject'])->middleware('throttle:10,1')->name('hr.payments.reject');



    Route::get('/contracts', [HrContractController::class, 'index'])->name('hr.contracts.index');

    Route::get('/contracts/create/{quotation}', [HrContractController::class, 'create'])->name('hr.contracts.create');

    Route::post('/contracts', [HrContractController::class, 'store'])

        ->middleware('throttle:10,1')

        ->name('hr.contracts.store');



    Route::get('/contracts/{contract}/edit', [HrContractController::class, 'edit'])

        ->name('hr.contracts.edit');



    Route::put('/contracts/{contract}', [HrContractController::class, 'update'])

        ->middleware('throttle:10,1')

        ->name('hr.contracts.update');



    Route::patch('/contracts/{contract}/void', [HrContractController::class, 'void'])

        ->middleware('throttle:10,1')

        ->name('hr.contracts.void');



    Route::get('/contracts/{contract}', [HrContractController::class, 'show'])

        ->name('hr.contracts.show');



    Route::post('/contracts/{contract}/finalize', [HrContractController::class, 'finalize'])

        ->middleware('throttle:10,1')

        ->name('hr.contracts.finalize');



    Route::get('/receipts', [HrReceiptController::class, 'index'])->name('hr.receipts.index');

    Route::get('/receipts/create/{payment}', [HrReceiptController::class, 'create'])->name('hr.receipts.create');

    Route::post('/receipts', [HrReceiptController::class, 'store'])->middleware('throttle:10,1')->name('hr.receipts.store');

    Route::get('/receipts/{receipt}', [HrReceiptController::class, 'show'])->name('hr.receipts.show');



    Route::get('/reports', [HrReportController::class, 'index'])->name('hr.reports.index');

    Route::get('/reports/income/export', [HrReportController::class, 'exportIncome'])->name('hr.reports.income.export');



    Route::post('/logout', [LoginController::class, 'logout'])->name('hr.logout');
});



/*

|--------------------------------------------------------------------------

| Inspector panel

|--------------------------------------------------------------------------

*/

Route::middleware(['auth', 'inspector'])->prefix('inspector')->group(function () {

    Route::get('/dashboard', [InspectorDashboardController::class, 'index'])->name('inspector.dashboard');



    Route::get('/requests', [InspectorQuotationController::class, 'index'])->name('inspector.quotations.index');

    Route::get('/requests/{quotation}', [InspectorQuotationController::class, 'show'])->name('inspector.quotations.show');

    Route::post('/requests/{quotation}/update', [InspectorQuotationController::class, 'update'])->middleware('throttle:20,1')->name('inspector.quotations.update');

    Route::post('/requests/{quotation}/inspection-status', [InspectorQuotationController::class, 'updateInspectionStatus'])->middleware('throttle:20,1')->name('inspector.quotations.inspection-status');



    Route::post('/requests/{quotation}/inspection-report', [InspectorQuotationController::class, 'saveInspectionReport'])->middleware('throttle:10,1')->name('inspector.quotations.inspection-report');



    Route::get('/availability', [InspectorAvailabilityController::class, 'index'])->name('inspector.availability.index');

    Route::post('/availability', [InspectorAvailabilityController::class, 'store'])->middleware('throttle:20,1')->name('inspector.availability.store');

    Route::post('/availability/{availability}/update', [InspectorAvailabilityController::class, 'update'])->middleware('throttle:20,1')->name('inspector.availability.update');

    Route::delete('/availability/{availability}', [InspectorAvailabilityController::class, 'destroy'])->middleware('throttle:20,1')->name('inspector.availability.destroy');

    Route::post('/requests/{quotation}/inspection-photos', [InspectorQuotationController::class, 'uploadInspectionPhotos'])->middleware('throttle:10,1')->name('inspector.quotations.inspection-photos.upload');

    Route::delete('/requests/{quotation}/inspection-photos/{inspectionPhoto}', [InspectorQuotationController::class, 'deleteInspectionPhoto'])->middleware('throttle:20,1')->name('inspector.quotations.inspection-photos.delete');

    Route::post('/requests/{quotation}/inspection-checklist', [InspectorQuotationController::class, 'updateChecklist'])->middleware('throttle:20,1')->name('inspector.quotations.inspection-checklist.update');

    Route::put('/profile', [InspectorProfileController::class, 'update'])->middleware('throttle:10,1')->name('inspector.profile.update');

    Route::get('/alerts', [InspectorAlertController::class, 'index'])->name('inspector.alerts.index');



    Route::post('/logout', [LoginController::class, 'logout'])->name('inspector.logout');
});
