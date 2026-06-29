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
use App\Http\Controllers\ParentPaymentController;

use App\Http\Controllers\LipilaWebhookController;
use App\Http\Services\LencoService;



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
// Parent Payment Routes
Route::get('/parent/search', [ParentPaymentController::class, 'searchPage'])->name('parent.search.page');
Route::post('/parent/search', [ParentPaymentController::class, 'searchParent'])->name('parent.search');
Route::get('/parent/payments/{pupilId}', [ParentPaymentController::class, 'showPayments'])->name('parent.payments');
Route::post('/parent/pay/{paymentId}', [ParentPaymentController::class, 'processPayment'])->name('parent.pay');
Route::get('/parent/payment/success', [ParentPaymentController::class, 'paymentSuccess'])->name('parent.payment.success');
Route::post('/tumeny/webhook', [ParentPaymentController::class, 'tumenyWebhook'])->name('tumeny.webhook');

// routes/web.php
Route::get('/parent/otp',         [ParentPaymentController::class, 'otpPage'])->name('parent.otp.page');
Route::post('/parent/otp/verify', [ParentPaymentController::class, 'verifyOtp'])->name('parent.otp.verify');
Route::post('/parent/otp/resend', [ParentPaymentController::class, 'resendOtp'])->name('parent.otp.resend');

Route::get('/parent/payment/status', [ParentPaymentController::class, 'checkPaymentStatus'])->name('parent.payment.status');
Route::get('/parent/payment/poll-status', [ParentPaymentController::class, 'pollStatus'])->name('parent.payment.poll');


// Add this near the Tumeny webhook line — outside any auth middleware

Route::get('/parent/payment/poll-status', [ParentPaymentController::class, 'pollStatus'])->name('parent.payment.poll');



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
    });
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
    Route::get('/examResults/exportPdf/{pupil}/{term}', [ExamController::class, 'exportPdf'])->name('examResults.exportPdf');

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
    Route::resource('subjects', SubjectController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::post('/expenses/export-report', [ExpenseController::class, 'exportReport'])->name('expenses.exportReport');

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

        Route::get('/report', [IncomeController::class, 'report'])->name('report');
    });

    Route::get('incomes/financial-report', [IncomeController::class, 'financialReport'])->name('financial.report');
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

    Route::prefix('incomes')->name('incomes.')->group(function() {
        Route::get('/', [IncomeController::class, 'index'])->name('index');
        Route::get('/create', [IncomeController::class, 'create'])->name('create');
        Route::post('/', [IncomeController::class, 'store'])->name('store');
        Route::get('/{income}/edit', [IncomeController::class, 'edit'])->name('edit');
        Route::put('/{income}', [IncomeController::class, 'update'])->name('update');
        Route::delete('/{income}', [IncomeController::class, 'destroy'])->name('destroy');

        Route::get('/report', [IncomeController::class, 'report'])->name('report');
    });

    Route::get('incomes/financial-report', [IncomeController::class, 'financialReport'])->name('financial.report');
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
    Route::get('/exam-results/positions', [ExamResultController::class, 'positions'])->name('examResults.positions');
    // Route::get('/expenses/delete/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
});

Route::get('/subscription/upgrade', function () {
    return view('subscription.upgrade');
})->name('subscription.upgrade');

Route::get('/results/send-sms/', [ResultsController::class, 'sendResults']) ->name('results.sendSms');

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

// ─── Lenco Payment Test Route ─────────────────────────────────────────────────
Route::get('/debug-lenco', function () {
    try {
        $lenco = new \App\Services\LencoService();

        $result = $lenco->collectMobileMoney([
            'amount'   => 1.00,              // ZMW 1 test amount
            'phone'    => '0973228432',      // ← your real number
            'operator' => 'airtel',          // 'airtel' | 'mtn' | 'zamtel'
            'bearer'   => 'merchant',
        ]);

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

// Poll status by reference (e.g. /debug-lenco-status?ref=PAY-XXXXXXXXXXXX)
Route::get('/debug-lenco-status', function (\Illuminate\Http\Request $request) {
    $ref = $request->query('ref');

    if (! $ref) {
        return response()->json(['error' => 'Pass ?ref=PAY-XXXX in the URL'], 400);
    }

    $lenco  = new \App\Services\LencoService();
    $result = $lenco->checkStatus($ref);

    return response()->json([
        'reference' => $ref,
        'result'    => $result,
    ]);
});

// Lenco webhook (outside auth + CSRF exempt)
Route::post('/lenco/callback', function (\Illuminate\Http\Request $request) {
    $rawBody  = $request->getContent();
    $sigHeader = $request->header('X-Lenco-Signature', '');

    $lenco = new \App\Services\LencoService();

    if (! $lenco->verifyWebhookSignature($rawBody, $sigHeader)) {
        \Illuminate\Support\Facades\Log::warning('Lenco webhook: invalid signature');
        return response()->json(['error' => 'Invalid signature'], 401);
    }

    $payload = $request->all();
    \Illuminate\Support\Facades\Log::info('Lenco webhook received', $payload);

    // TODO: update your payment record based on $payload['status']

    return response()->json(['status' => 'received'], 200);
})->name('lenco.callback');