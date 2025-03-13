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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

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
    Route::resource('teachers', TeacherController::class);
    Route::resource('secretaries', SecretaryController::class);
    Route::resource('subjects', SubjectController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::resource('incomes', IncomeController::class);

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

    // Route::get('payments/select-pupil', [PaymentController::class, 'selectPupil'])->name('payments.select-pupil');
    // Route::get('payments/create/{pupil}', [PaymentController::class, 'create'])->name('payments.create');
    // Route::get('payments/{payment}', [PaymentController::class, 'show'])->name('payments.show');
    // Route::get('payments/pay-balance/{payment}', [PaymentController::class, 'createPayBalance'])->name('payments.create-pay-balance');
    // Route::post('payments/pay-balance/{payment}', [PaymentController::class, 'payBalance'])->name('payments.pay-balance');
    // Route::get('payments/', [PaymentController::class, 'index'])->name('payments.index');
    // Route::post('payments/', [PaymentController::class, 'store'])->name('payments.store');
    // Route::get('payments/export-pdf/{payment}', [PaymentController::class, 'exportPdf'])->name('payments.export-pdf');
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
