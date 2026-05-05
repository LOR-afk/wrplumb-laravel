<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicQuotationController;
use App\Http\Controllers\Admin\Auth\AdminLoginController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\QuotationController;
use App\Http\Controllers\Admin\SupportController as AdminSupportController;
use App\Http\Controllers\Admin\AlertController as AdminAlertController;
use App\Http\Controllers\Client\DashboardController as ClientDashboardController;
use App\Http\Controllers\Client\SupportController as ClientSupportController;
use App\Http\Controllers\Client\MyRequestController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Hr\SupportController as HrSupportController;
use App\Http\Controllers\Hr\DashboardController as HrDashboardController;
use App\Http\Controllers\Hr\Auth\HrLoginController;
use App\Http\Controllers\Inspector\Auth\InspectorLoginController;
use App\Http\Controllers\Inspector\DashboardController as InspectorDashboardController;
use App\Http\Controllers\Inspector\QuotationController as InspectorQuotationController;
use App\Http\Controllers\Inspector\AvailabilityController as InspectorAvailabilityController;
use App\Http\Controllers\Admin\InspectorAvailabilityController as AdminInspectorAvailabilityController;
use App\Http\Controllers\Client\AlertController as ClientAlertController;
use App\Http\Controllers\Inspector\AlertController as InspectorAlertController;
use App\Http\Controllers\Hr\QuotationController as HrQuotationController;
use App\Http\Controllers\Client\QuotationController as ClientQuotationController;
use App\Http\Controllers\Hr\InvoiceController as HrInvoiceController;
use App\Http\Controllers\Client\InvoiceController as ClientInvoiceController;
use App\Http\Controllers\Hr\PaymentController as HrPaymentController;
use App\Http\Controllers\Client\PaymentController as ClientPaymentController;
use App\Http\Controllers\Hr\ContractController as HrContractController;
use App\Http\Controllers\Client\ContractController as ClientContractController;
use App\Http\Controllers\Hr\ReceiptController as HrReceiptController;
use App\Http\Controllers\Client\ReceiptController as ClientReceiptController;
use App\Http\Controllers\Admin\QuotationController as AdminQuotationController;
use App\Http\Controllers\Admin\JobOrderController as AdminJobOrderController;
use App\Http\Controllers\Client\JobOrderController as ClientJobOrderController;
use App\Http\Controllers\Hr\ReportController as HrReportController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\PublicQuotationAcceptanceController;


/*|--------------------------------------------------------------------------
| Routes for public pages and quotation acceptance
|--------------------------------------------------------------------------
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which contains the "web" middleware group. Now create something great!   |
*/
Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/internal-portal', function () {
    return view('landing.internal');
})->name('landing.internal');

Route::post('/free-quotation', [PublicQuotationController::class, 'store'])
    ->name('free-quotation.store');

Route::get('/quotation/view/{token}', [PublicQuotationAcceptanceController::class, 'show'])
    ->name('public.quotations.show');

Route::post('/quotation/view/{token}/accept', [PublicQuotationAcceptanceController::class, 'accept'])
    ->name('public.quotations.accept');

Route::post('/quotation/view/{token}/decline', [PublicQuotationAcceptanceController::class, 'decline'])
    ->name('public.quotations.decline');

/*
|--------------------------------------------------------------------------
| Client auth
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::get('/client/dashboard', [ClientDashboardController::class, 'index'])->name('client.dashboard');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/client/support', [ClientSupportController::class, 'index'])->name('client.support.index');
    Route::post('/client/support/send', [ClientSupportController::class, 'sendMessage'])->name('client.support.send');
    
    Route::get('/client/invoices', [ClientInvoiceController::class, 'index'])->name('client.invoices.index');
    Route::get('/client/invoices/{invoice}', [ClientInvoiceController::class, 'show'])->name('client.invoices.show');
    Route::get('/client/requests', [MyRequestController::class, 'index'])->name('client.requests.index');
    Route::get('/client/requests/create', [MyRequestController::class, 'create'])->name('client.requests.create');
    Route::post('/client/requests', [MyRequestController::class, 'store'])->name('client.requests.store');

    Route::get('/client/availability/calendar', [MyRequestController::class, 'calendarAvailability'])->name('client.requests.calendar-availability');
    
    Route::get('/client/quotations', [ClientQuotationController::class, 'index'])->name('client.quotations.index');
    Route::get('/client/quotations/{quotation}', [ClientQuotationController::class, 'show'])->name('client.quotations.show');
    Route::get('/client/contracts', [ClientContractController::class, 'index'])->name('client.contracts.index');
    Route::get('/client/contracts/{contract}', [ClientContractController::class, 'show'])->name('client.contracts.show');
    Route::post('/client/contracts/{contract}/accept', [ClientContractController::class, 'accept'])->name('client.contracts.accept');
    Route::get('/client/requests/{quotation}', [MyRequestController::class, 'show'])->name('client.requests.show');
    Route::post('/client/requests/{quotation}/request-reschedule', [MyRequestController::class, 'requestReschedule'])
        ->name('client.requests.request-reschedule');
    Route::post('/client/requests/{quotation}/request-cancel', [MyRequestController::class, 'requestCancel'])
        ->name('client.requests.request-cancel');
    Route::get('/client/payments', [ClientPaymentController::class, 'index'])->name('client.payments.index');
    Route::get('/client/payments/{payment}', [ClientPaymentController::class, 'show'])->name('client.payments.show');
    Route::get('/client/invoices/{invoice}/payments/create', [ClientPaymentController::class, 'create'])->name('client.payments.create');
    Route::post('/client/payments', [ClientPaymentController::class, 'store'])->name('client.payments.store');
    Route::get('/client/receipts', [ClientReceiptController::class, 'index'])->name('client.receipts.index');
    Route::get('/client/receipts/{receipt}', [ClientReceiptController::class, 'show'])->name('client.receipts.show');
    Route::get('/client/job-orders', [ClientJobOrderController::class, 'index'])->name('client.job-orders.index');
    Route::get('/client/job-orders/{jobOrder}', [ClientJobOrderController::class, 'show'])->name('client.job-orders.show'); 

    Route::get('/client/alerts', [ClientAlertController::class, 'index'])->name('client.alerts.index');
});

/*
|--------------------------------------------------------------------------
| Admin auth with OTP
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->prefix('admin')->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLogin'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'sendOtp'])->name('admin.login.send-otp');

    Route::get('/verify-otp', [AdminLoginController::class, 'showOtpForm'])->name('admin.otp.form');
    Route::post('/verify-otp', [AdminLoginController::class, 'verifyOtp'])->name('admin.otp.verify');
    Route::post('/verify-otp/resend', [AdminLoginController::class, 'resendOtp'])
    ->name('admin.otp.resend')
    ->middleware('throttle:3,1');

});

/*
|--------------------------------------------------------------------------
| Admin panel
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

    Route::get('/inspectors/availability', [AdminInspectorAvailabilityController::class, 'index'])->name('admin.inspectors.availability');
    Route::get('/clients', [ClientController::class, 'index'])->name('admin.clients.index');
    Route::patch('/clients/{user}/toggle-status', [ClientController::class, 'toggleStatus'])->name('admin.clients.toggle-status');

    Route::get('/quotations', [QuotationController::class, 'index'])->name('admin.quotations.index');
    Route::post('/quotations/{quotation}/assign-worker', [QuotationController::class, 'assignWorker'])->name('admin.quotations.assign-worker');
    Route::post('/quotations/{quotation}/appointment', [QuotationController::class, 'updateAppointment'])->name('admin.quotations.update-appointment');
    Route::post('/quotations/{quotation}/client-request-review', [QuotationController::class, 'reviewClientRequest'])
        ->name('admin.quotations.review-client-request');
    Route::patch('/admin/quotations/{quotation}/flow', [AdminQuotationController::class, 'updateFlow'])
    ->name('admin.quotations.update-flow');

    Route::get('/support', [AdminSupportController::class, 'index'])->name('admin.support.index');
    Route::get('/support/{conversation}', [AdminSupportController::class, 'show'])->name('admin.support.show');
    Route::post('/support/{conversation}/reply', [AdminSupportController::class, 'reply'])->name('admin.support.reply');
    Route::post('/support/{conversation}/resolve', [AdminSupportController::class, 'resolve'])->name('admin.support.resolve');

    Route::get('/admin/job-orders', [AdminJobOrderController::class, 'index'])->name('admin.job-orders.index');
    Route::get('/admin/job-orders/create/{quotation}', [AdminJobOrderController::class, 'create'])->name('admin.job-orders.create');
    Route::post('/admin/job-orders', [AdminJobOrderController::class, 'store'])->name('admin.job-orders.store');
    Route::get('/admin/job-orders/{jobOrder}', [AdminJobOrderController::class, 'show'])->name('admin.job-orders.show');
    Route::patch('/admin/job-orders/{jobOrder}/start', [AdminJobOrderController::class, 'start'])->name('admin.job-orders.start');
    Route::patch('/admin/job-orders/{jobOrder}/complete', [AdminJobOrderController::class, 'complete'])->name('admin.job-orders.complete');
    Route::patch('/admin/job-orders/{jobOrder}/cancel', [AdminJobOrderController::class, 'cancel'])->name('admin.job-orders.cancel');
    Route::patch('/admin/job-orders/{jobOrder}/remarks', [AdminJobOrderController::class, 'updateRemarks'])->name('admin.job-orders.update-remarks');
    Route::get('/reports', [AdminReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/projects/export', [AdminReportController::class, 'exportProjects'])->name('admin.reports.projects.export');

    Route::get('/alerts', [AdminAlertController::class, 'index'])->name('admin.alerts.index');

    Route::post('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
});

/*
|--------------------------------------------------------------------------
| HR panel
|--------------------------------------------------------------------------
*/


Route::middleware('guest')->prefix('hr')->group(function () {
    Route::get('/login', [HrLoginController::class, 'show'])->name('hr.login');
    Route::post('/login', [HrLoginController::class, 'authenticate'])->name('hr.login.attempt');
});

Route::middleware(['auth', 'hr'])->prefix('hr')->group(function () {
    Route::get('/dashboard', [HrDashboardController::class, 'index'])->name('hr.dashboard');

    Route::get('/quotations', [HrQuotationController::class, 'index'])->name('hr.quotations.index');
    Route::get('/quotations/create/{quotationRequest}', [HrQuotationController::class, 'create'])->name('hr.quotations.create');
    Route::post('/quotations', [HrQuotationController::class, 'store'])->name('hr.quotations.store');
    Route::get('/quotations/{quotation}', [HrQuotationController::class, 'show'])->name('hr.quotations.show');
    Route::post('/quotations/{quotation}/send', [HrQuotationController::class, 'send'])->name('hr.quotations.send');

    Route::get('/invoices', [HrInvoiceController::class, 'index'])->name('hr.invoices.index');
    Route::get('/invoices/create/{quotation}', [HrInvoiceController::class, 'create'])->name('hr.invoices.create');
    Route::post('/invoices', [HrInvoiceController::class, 'store'])->name('hr.invoices.store');
    Route::get('/invoices/{invoice}', [HrInvoiceController::class, 'show'])->name('hr.invoices.show');
    Route::get('/support', [HrSupportController::class, 'index'])->name('hr.support.index');
    Route::get('/support/{conversation}', [HrSupportController::class, 'show'])->name('hr.support.show');
    Route::post('/support/{conversation}/reply', [HrSupportController::class, 'reply'])->name('hr.support.reply');
    Route::post('/support/{conversation}/escalate-admin', [HrSupportController::class, 'escalateToAdmin'])->name('hr.support.escalate-admin');
    Route::get('/payments', [HrPaymentController::class, 'index'])->name('hr.payments.index');
    Route::get('/payments/create/{invoice}', [HrPaymentController::class, 'create'])->name('hr.payments.create');
    Route::post('/payments', [HrPaymentController::class, 'store'])->name('hr.payments.store');
    Route::get('/payments/{payment}', [HrPaymentController::class, 'show'])->name('hr.payments.show');
    Route::post('/payments/{payment}/confirm', [HrPaymentController::class, 'confirm'])->name('hr.payments.confirm');
    Route::post('/payments/{payment}/reject', [HrPaymentController::class, 'reject'])->name('hr.payments.reject');
    Route::get('/contracts', [HrContractController::class, 'index'])->name('hr.contracts.index');
    Route::get('/contracts/create/{quotation}', [HrContractController::class, 'create'])->name('hr.contracts.create');
    Route::post('/contracts', [HrContractController::class, 'store'])->name('hr.contracts.store');
    Route::get('/contracts/{contract}', [HrContractController::class, 'show'])->name('hr.contracts.show');
    Route::post('/contracts/{contract}/finalize', [HrContractController::class, 'finalize'])->name('hr.contracts.finalize');
    Route::get('/receipts', [HrReceiptController::class, 'index'])->name('hr.receipts.index');
    Route::get('/receipts/create/{payment}', [HrReceiptController::class, 'create'])->name('hr.receipts.create');
    Route::post('/receipts', [HrReceiptController::class, 'store'])->name('hr.receipts.store');
    Route::get('/receipts/{receipt}', [HrReceiptController::class, 'show'])->name('hr.receipts.show');
    Route::get('/reports', [HrReportController::class, 'index'])->name('hr.reports.index');
    Route::get('/reports/income/export', [HrReportController::class, 'exportIncome'])->name('hr.reports.income.export');

    Route::post('/logout', [HrLoginController::class, 'logout'])->name('hr.logout');
});


/*
|--------------------------------------------------------------------------
| Inspector panel routes
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->prefix('inspector')->group(function () {
    Route::get('/login', [InspectorLoginController::class, 'show'])->name('inspector.login');
    Route::post('/login', [InspectorLoginController::class, 'authenticate'])->name('inspector.login.attempt');
});

Route::middleware(['auth', 'inspector'])->prefix('inspector')->group(function () {
    Route::get('/dashboard', [InspectorDashboardController::class, 'index'])->name('inspector.dashboard');

    Route::get('/requests', [InspectorQuotationController::class, 'index'])->name('inspector.quotations.index');
    Route::get('/requests/{quotation}', [InspectorQuotationController::class, 'show'])->name('inspector.quotations.show');
    Route::post('/requests/{quotation}/update', [InspectorQuotationController::class, 'update'])->name('inspector.quotations.update');

    Route::get('/availability', [InspectorAvailabilityController::class, 'index'])->name('inspector.availability.index');
    Route::post('/availability', [InspectorAvailabilityController::class, 'store'])->name('inspector.availability.store');
    Route::post('/availability/{availability}/update', [InspectorAvailabilityController::class, 'update'])->name('inspector.availability.update');
    Route::delete('/availability/{availability}', [InspectorAvailabilityController::class, 'destroy'])->name('inspector.availability.destroy');

    Route::get('/alerts', [InspectorAlertController::class, 'index'])->name('inspector.alerts.index');

    Route::post('/logout', [InspectorLoginController::class, 'logout'])->name('inspector.logout');
});