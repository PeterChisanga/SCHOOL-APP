<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\PupilController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\ResultsController;

use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryCategoryController;
use App\Http\Controllers\ParentPaymentController;
use App\Http\Controllers\Admin\AdminPaymentVerificationController;
use App\Http\Controllers\Admin\PaymentDetailController;



/*
|--------------------------------------------------------------------------
| Web Routes update
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// routes/web.php
Route::get('/parent/otp',         [ParentPaymentController::class, 'otpPage'])->name('parent.otp.page');
Route::post('/parent/otp/verify', [ParentPaymentController::class, 'verifyOtp'])->name('parent.otp.verify');
Route::post('/parent/otp/resend', [ParentPaymentController::class, 'resendOtp'])->name('parent.otp.resend');

Route::get('/parent/payment/status', [ParentPaymentController::class, 'checkPaymentStatus'])->name('parent.payment.status');
Route::get('/parent/payment/poll-status', [ParentPaymentController::class, 'pollStatus'])->name('parent.payment.poll');


// Add this near the Tumeny webhook line — outside any auth middleware


//// manual payment verification routes for admin

// --- Parent-facing: manual payment (bank transfer / reference / proof upload) ---
Route::get('/parent/payment/{paymentId}/manual', [ParentPaymentController::class, 'showManualPaymentForm'])
    ->name('parent.manual.payment.form');
 
Route::post('/parent/payment/{paymentId}/manual', [ParentPaymentController::class, 'submitManualPayment'])
    ->name('parent.manual.payment.submit');
 
Route::get('/parent/payment/manual/submitted', [ParentPaymentController::class, 'manualPaymentSubmitted'])
    ->name('parent.manual.payment.submitted');
 
// --- Admin: review pending manual payments ---
// Wrap these in whatever admin auth middleware you already use, e.g. ->middleware(['auth', 'admin'])
Route::prefix('admin/payment-verification')->name('admin.payment.verification.')->group(function () {
    Route::get('/', [AdminPaymentVerificationController::class, 'index'])->name('index');
    Route::post('/{transactionId}/approve', [AdminPaymentVerificationController::class, 'approve'])->name('approve');
    Route::post('/{transactionId}/reject', [AdminPaymentVerificationController::class, 'reject'])->name('reject');
});
 
// --- Admin: manage each school's payment details (bank / mobile money) ---
Route::prefix('admin/schools/{schoolId}/payment-details')->name('admin.payment.details.')->group(function () {
    Route::get('/', [PaymentDetailController::class, 'show'])->name('show');       // JSON fetch by school_id
    Route::get('/edit', [PaymentDetailController::class, 'edit'])->name('edit');   // form
    Route::post('/', [PaymentDetailController::class, 'upsert'])->name('upsert');  // create or update
});


// Parent Payment Routes
Route::get('/parent/search', [ParentPaymentController::class, 'searchPage'])->name('parent.search.page');
Route::post('/parent/search', [ParentPaymentController::class, 'searchParent'])->name('parent.search');
Route::get('/parent/payments/{pupilId}', [ParentPaymentController::class, 'showPayments'])->name('parent.payments');
Route::get('/parent/results/{pupilId}', [ParentPaymentController::class, 'showResults'])->name('parent.results');
// This route is for downloading results as PDF, it will be used in the results page for parents
Route::get('/examResults/exportPdf/{pupil}/{term}', [ExamController::class, 'exportPdf'])->name('examResults.exportPdf');
Route::post('/parent/pay/{paymentId}', [ParentPaymentController::class, 'processPayment'])->name('parent.pay');
Route::get('/parent/payment/success', [ParentPaymentController::class, 'paymentSuccess'])->name('parent.payment.success');
Route::post('/tumeny/webhook', [ParentPaymentController::class, 'tumenyWebhook'])->name('tumeny.webhook');

// Route::get('/parent/payment/payment-status', [ParentPaymentController::class, 'checkPaymentStatus'])->name('parent.payment.status');
// Route::post('/parent/payment/check-status', [ParentPaymentController::class, 'getPaymentStatus'])->name('parent.payment.check-status');
Route::get('/parent/payment/status', [ParentPaymentController::class, 'checkPaymentStatus'])->name('parent.payment.status');
Route::post('/parent/payment/get-status', [ParentPaymentController::class, 'getPaymentStatus'])->name('parent.payment.check-status');

Route::get('/', function () {
    return view('welcome');
});
Route::get('/about', function () {
    return view('about');
});
Route::get('/contact', function () {
    return view('contact');
});
Route::get('/products', function () {
    return view('course');
});

//Route::get('/payment', [ParentPaymentController::class, 'searchPage'])->name('payment.search');
Route::get('/payment', [ParentPaymentController::class, 'searchPage'])->name('payment.search');


// User routes
Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
// Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::post('/users', [UserController::class, 'store'])->name('users.store');
Route::get('/login', function () {
    return view('users.login');
})->name('users.login');

Route::post('/login', [UserController::class, 'login'])->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard');
    Route::get('/users/show', [UserController::class, 'show'])->name('users.show');
    Route::post('/change-password', [UserController::class, 'changePassword'])->name('change-password');
    Route::get('/logout', [UserController::class, 'logout'])->name('logout');

    Route::resource('pupils', PupilController::class);

    Route::get('parents/', [ParentController::class, 'index'])->name('parents.index');
    Route::post('parents/edit/{parent}', [ParentController::class, 'update'])->name('parents.update');
    Route::post('parents/', [ParentController::class, 'store'])->name('parents.store');
    Route::get('parents/create/{pupil}', [ParentController::class, 'create'])->name('parents.create');
    Route::get('parents/edit/{parent}', [ParentController::class, 'edit'])->name('parents.edit');

    Route::resource('examResults', ExamController::class);

    Route::resource('assessments', AssessmentController::class);

    Route::resource('subjects', SubjectController::class);
    Route::resource('classes', ClassController::class);
    Route::get('classes/exportPdf/{class}', [ClassController::class, 'exportPdf'])->name('classes.exportPdf');
    Route::resource('schools', SchoolController::class);
});

Route::group(['middleware' => 'admin'], function() {
    Route::get('/admin/dashboard', [UserController::class, 'adminDashboard'])->name('admin.dashboard');
    // Route::resource('teachers', TeacherController::class);
    Route::get('teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::get('teachers/create', [TeacherController::class, 'create'])->name('teachers.create');
    Route::post('teachers', [TeacherController::class, 'store'])->name('teachers.store');
    Route::get('teachers/{teacher}', [TeacherController::class, 'show'])->name('teachers.show');
    Route::get('teachers/{teacher}/edit', [TeacherController::class, 'edit'])->name('teachers.edit');
    Route::put('teachers/{teacher}', [TeacherController::class, 'update'])->name('teachers.update');
    Route::delete('teachers/{teacher}', [TeacherController::class, 'destroy'])->name('teachers.destroy');

    Route::resource('secretaries', SecretaryController::class);
    Route::resource('expenses', ExpenseController::class);
    // Expense report routes
    Route::post('expenses/export-report',       [ExpenseController::class, 'exportReport'])->name('expenses.exportReport');
    Route::post('expenses/export-report/excel', [ExpenseController::class, 'exportReportExcel'])->name('expenses.exportReport.excel');
    Route::post('expenses/export-report/word',  [ExpenseController::class, 'exportReportWord'])->name('expenses.exportReport.word');

    Route::prefix('payments')->name('payments.')->group(function() {
        Route::get('/select-pupil', [PaymentController::class, 'selectPupil'])->name('select-pupil');
        Route::get('/create/{pupil}', [PaymentController::class, 'create'])->name('create');
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
        Route::get('/pay-balance/{payment}', [PaymentController::class, 'createPayBalance'])->name('create-pay-balance');
        Route::post('/pay-balance/{payment}', [PaymentController::class, 'payBalance'])->name('pay-balance');
        Route::get('/export-pdf/{payment}', [PaymentController::class, 'exportPdf'])->name('export-pdf');
    });

    Route::prefix('incomes')->name('incomes.')->group(function() {
        Route::get('/', [IncomeController::class, 'index'])->name('index');
        Route::get('/create', [IncomeController::class, 'create'])->name('create');
        Route::post('/', [IncomeController::class, 'store'])->name('store');
        Route::get('/{income}/edit', [IncomeController::class, 'edit'])->name('edit');
        Route::put('/{income}', [IncomeController::class, 'update'])->name('update');
        Route::delete('/{income}', [IncomeController::class, 'destroy'])->name('destroy');
        
        // Income report routes
        Route::get('/report', [IncomeController::class, 'report'])->name('report');
        Route::get('/report/excel', [IncomeController::class, 'reportExcel'])->name('report.excel');
        Route::get('/report/word',  [IncomeController::class, 'reportWord'])->name('report.word');
    });

    Route::get('incomes/financial-report', [IncomeController::class, 'financialReport'])->name('financial.report');
    Route::get('incomes/financial-report/excel', [IncomeController::class, 'financialReportExcel'])->name('financial.report.excel');
    Route::get('incomes/financial-report/word',  [IncomeController::class, 'financialReportWord'])->name('financial.report.word');
});


Route::group(['middleware' => 'secretary'], function() {
    Route::get('/secretary/dashboard', [UserController::class, 'secretaryDashboard'])->name('secretary.dashboard');

    Route::prefix('payments')->name('payments.')->group(function() {
        Route::get('/select-pupil', [PaymentController::class, 'selectPupil'])->name('select-pupil');
        Route::get('/create/{pupil}', [PaymentController::class, 'create'])->name('create');
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::post('/', [PaymentController::class, 'store'])->name('store');
        Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
        Route::get('/pay-balance/{payment}', [PaymentController::class, 'createPayBalance'])->name('create-pay-balance');
        Route::post('/pay-balance/{payment}', [PaymentController::class, 'payBalance'])->name('pay-balance');
        Route::get('/export-pdf/{payment}', [PaymentController::class, 'exportPdf'])->name('export-pdf');
    });

    Route::resource('expenses', ExpenseController::class);
    // Expense report routes
    Route::post('expenses/export-report',       [ExpenseController::class, 'exportReport'])->name('expenses.exportReport');
    Route::post('expenses/export-report/excel', [ExpenseController::class, 'exportReportExcel'])->name('expenses.exportReport.excel');
    Route::post('expenses/export-report/word',  [ExpenseController::class, 'exportReportWord'])->name('expenses.exportReport.word');

    Route::prefix('incomes')->name('incomes.')->group(function() {
        Route::get('/', [IncomeController::class, 'index'])->name('index');
        Route::get('/create', [IncomeController::class, 'create'])->name('create');
        Route::post('/', [IncomeController::class, 'store'])->name('store');
        Route::get('/{income}/edit', [IncomeController::class, 'edit'])->name('edit');
        Route::put('/{income}', [IncomeController::class, 'update'])->name('update');
        Route::delete('/{income}', [IncomeController::class, 'destroy'])->name('destroy');

        // Income report routes
        Route::get('/report', [IncomeController::class, 'report'])->name('report');
        Route::get('/report/excel', [IncomeController::class, 'reportExcel'])->name('report.excel');
        Route::get('/report/word',  [IncomeController::class, 'reportWord'])->name('report.word');
    });

    Route::get('incomes/financial-report', [IncomeController::class, 'financialReport'])->name('financial.report');
    Route::get('incomes/financial-report/excel', [IncomeController::class, 'financialReportExcel'])->name('financial.report.excel');
    Route::get('incomes/financial-report/word',  [IncomeController::class, 'financialReportWord'])->name('financial.report.word');
});

Route::group(['middleware' => 'teacher'], function() {
    Route::get('/teacher/dashboard', [UserController::class, 'teacherDashboard'])->name('teacher.dashboard');

});

Route::group(['middleware' => 'parent'], function() {
    Route::get('parent/dashboard', function () {
        return view('not-yet-implemented');
    });
});

Route::group(['middleware' => 'student'], function() {
    Route::get('parent/dashboard', function () {
        return view('not-yet-implemented');
    });
});

Route::middleware(['auth', 'premium'])->group(function () {
    // Route::get('/exam-results/positions', [ExamResultController::class, 'positions'])->name('examResults.positions');
    // Route::get('/expenses/delete/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    Route::resource('inventory/categories', InventoryCategoryController::class)
        ->names('inventory.categories');
         // Inventory list & filtering

    // Inventory CRUD
    Route::resource('inventory', InventoryController::class);

    // Stock operations
    Route::post('inventory/{inventory}/add-stock',
        [InventoryController::class, 'addStock'])
        ->name('inventory.addStock');

    Route::post('inventory/{inventory}/remove-stock',
        [InventoryController::class, 'removeStock'])
        ->name('inventory.removeStock');

    Route::get('inventory/{inventory}/activity',
        [InventoryController::class, 'activity'])
        ->name('inventory.activity');

    // Inventory reports
    Route::get('inventory/reports/movement/pdf',
        [InventoryController::class, 'movementReport']
        )->name('inventory.reports.movement.pdf');

    Route::get('inventory/reports/summary/pdf',
        [InventoryController::class, 'summaryReport']
        )->name('inventory.reports.summary.pdf');

});

Route::get('/subscription/upgrade', function () {
    return view('subscription.upgrade');
})->name('subscription.upgrade');


Route::middleware(['auth'])->group(function () {
    Route::post('/results/send-sms', [ResultsController::class, 'sendResults'])->name('results.sendSms');
});

Route::get('/debug-sms', function () {
    try {
        $sms    = new \App\Services\AfricasTalkingService();
        $result = $sms->sendSms('+260973228432', 'Test OTP: 123456'); // ← your real number

        return response()->json([
            'success' => true,
            'result'  => $result,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error'   => $e->getMessage(),
            'trace'   => $e->getTraceAsString(),
        ]);
    }
});

// Lenco webhook (outside auth + CSRF exempt) — handled by ParentPaymentController::paymentWebhook,
// which verifies the signature and credits the payment balance via applyToBalance.
Route::post('/lenco/callback', [ParentPaymentController::class, 'paymentWebhook'])
    ->name('lenco.callback');
