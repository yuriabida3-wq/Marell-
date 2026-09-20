<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\AdmissionController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ParentPortalController;
use App\Http\Controllers\PrincipalController;
use App\Http\Controllers\StudentAdminController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\SmsCenterController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\DosController;
use App\Http\Controllers\Auth\LoginController;

// Auth
Route::get('/login',   [LoginController::class, 'show'])->name('login');
Route::post('/login',  [LoginController::class, 'login'])->middleware('throttle:6,1');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Public site
Route::get('/',            [PublicController::class, 'home'])->name('home');
Route::get('/about',       [PublicController::class, 'about'])->name('about');
Route::get('/academics',   [PublicController::class, 'academics'])->name('academics');
Route::get('/fees',        [PublicController::class, 'fees'])->name('fees');
Route::get('/admissions',  [PublicController::class, 'admissions'])->name('admissions');
Route::post('/admissions', [AdmissionController::class, 'submit'])->name('admissions.submit');
Route::get('/results',     [PublicController::class, 'results'])->name('results');
Route::get('/contact',     [PublicController::class, 'contact'])->name('contact');
Route::post('/contact',    [ContactController::class, 'submit'])->name('contact.submit');

// Parent Portal — Pay
Route::middleware('throttle:60,1')->group(function () {
Route::get('/pay',                   [PaymentController::class, 'show'])->name('pay');
Route::post('/pay',                  [PaymentController::class, 'initiate'])->middleware('throttle:10,1')->name('pay.initiate');
Route::get('/pay/wait/{payment}',    [PaymentController::class, 'wait'])->name('pay.wait');
Route::get('/pay/check/{payment}',   [PaymentController::class, 'check'])->name('pay.check');
Route::get('/pay/success/{payment}', [PaymentController::class, 'success'])->name('pay.success');
Route::get('/pay/receipt/{payment}', [PaymentController::class, 'receipt'])->middleware('signed')->name('pay.receipt');
});

// Parent Portal — Auth
Route::get('/parent/login',     [ParentPortalController::class, 'loginForm'])->name('parent.login');
Route::post('/parent/send-otp', [ParentPortalController::class, 'sendOtp'])->middleware('throttle:5,1')->name('parent.send-otp');
Route::get('/parent/verify',    [ParentPortalController::class, 'verifyForm'])->name('parent.verify');
Route::post('/parent/verify',   [ParentPortalController::class, 'verifyOtp'])->middleware('throttle:10,1')->name('parent.verify-otp');
Route::post('/parent/logout',   [ParentPortalController::class, 'logout'])->name('parent.logout');

Route::middleware('parent.auth')->group(function () {
    Route::get('/parent', [ParentPortalController::class, 'dashboard'])->name('parent.dashboard');
});

// Principal Admin
Route::middleware(['auth', 'role:principal'])->prefix('principal')->name('principal.')->group(function () {
    Route::get('/',                [PrincipalController::class, 'dashboard'])->name('dashboard');
    Route::get('/search',          [PrincipalController::class, 'search'])->name('search');
    Route::get('/export/students', [PrincipalController::class, 'exportStudents'])->name('export-students');

    Route::get('/students',                    [StudentAdminController::class, 'index'])->name('students.index');
    Route::get('/students/create',             [StudentAdminController::class, 'create'])->name('students.create');
    Route::post('/students',                   [StudentAdminController::class, 'store'])->name('students.store');
    Route::get('/students/import',             [StudentAdminController::class, 'importForm'])->name('students.import');
    Route::post('/students/import',            [StudentAdminController::class, 'import'])->name('students.import.store');
    Route::get('/students/{student}',          [StudentAdminController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/edit',     [StudentAdminController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}',          [StudentAdminController::class, 'update'])->name('students.update');
    Route::post('/students/{student}/toggle',  [StudentAdminController::class, 'toggleStatus'])->name('students.toggle');

    Route::get('/finance',                  [FinanceController::class, 'index'])->name('finance.index');
    Route::get('/finance/record',           [FinanceController::class, 'recordForm'])->name('finance.record');
    Route::post('/finance/record',          [FinanceController::class, 'record'])->name('finance.record.store');
    Route::get('/finance/defaulters',       [FinanceController::class, 'defaulters'])->name('finance.defaulters');
    Route::get('/finance/daily',            [FinanceController::class, 'daily'])->name('finance.daily');
    Route::get('/finance/export',           [FinanceController::class, 'exportPayments'])->name('finance.export');
    Route::get('/finance/export-defaulters',[FinanceController::class, 'exportDefaulters'])->name('finance.export-defaulters');

    Route::get('/sms',  [SmsCenterController::class, 'index'])->name('sms.index');
    Route::post('/sms', [SmsCenterController::class, 'send'])->name('sms.send');

    Route::get('/users',                       [UserAdminController::class, 'index'])->name('users.index');
    Route::get('/users/create',                [UserAdminController::class, 'create'])->name('users.create');
    Route::post('/users',                      [UserAdminController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit',           [UserAdminController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}',                [UserAdminController::class, 'update'])->name('users.update');
    Route::post('/users/{user}/reset-password',[UserAdminController::class, 'resetPassword'])->name('users.reset-password');
    Route::post('/users/{user}/toggle',        [UserAdminController::class, 'toggle'])->name('users.toggle');
});

// DOS Academic
Route::middleware(['auth', 'role:dos|principal'])->prefix('dos')->name('dos.')->group(function () {
    Route::get('/', [DosController::class, 'dashboard'])->name('dashboard');

    // Classes
    Route::get('/classes',                 [DosController::class, 'classesIndex'])->name('classes.index');
    Route::post('/classes',                [DosController::class, 'classesStore'])->name('classes.store');
    Route::put('/classes/{classroom}',     [DosController::class, 'classesUpdate'])->name('classes.update');
    Route::delete('/classes/{classroom}',  [DosController::class, 'classesDestroy'])->name('classes.destroy');

    // Timetable
    Route::get('/timetable',                 [DosController::class, 'timetableIndex'])->name('timetable.index');
    Route::post('/timetable/generate',       [DosController::class, 'timetableGenerate'])->name('timetable.generate');
    Route::get('/timetable/teacher',         [DosController::class, 'timetableTeacher'])->name('timetable.teacher');
    Route::post('/timetable/manual',         [DosController::class, 'timetableManual'])->name('timetable.manual');
    Route::get('/timetable/{classroom}',     [DosController::class, 'timetableByClass'])->name('timetable.show');
    Route::get('/timetable/{classroom}/pdf', [DosController::class, 'timetablePdf'])->name('timetable.pdf');
});

// DOS — Exams + Marks
Route::middleware(['auth', 'role:dos|principal'])->prefix('dos')->name('dos.')->group(function () {
    Route::get('/exams',                          [App\Http\Controllers\DosExamController::class, 'index'])->name('exams.index');
    Route::get('/exams/create',                   [App\Http\Controllers\DosExamController::class, 'create'])->name('exams.create');
    Route::post('/exams',                         [App\Http\Controllers\DosExamController::class, 'store'])->name('exams.store');
    Route::get('/exams/{exam}',                   [App\Http\Controllers\DosExamController::class, 'show'])->name('exams.show');
    Route::post('/exams/{exam}/status',           [App\Http\Controllers\DosExamController::class, 'updateStatus'])->name('exams.status');
    Route::post('/exams/{exam}/publish',          [App\Http\Controllers\DosExamController::class, 'publish'])->name('exams.publish');

    Route::get('/marks',                          [App\Http\Controllers\DosExamController::class, 'marksSelect'])->name('marks');
    Route::post('/marks',                         [App\Http\Controllers\DosExamController::class, 'marksStore'])->name('marks.store');
    Route::get('/missing-marks',                  [App\Http\Controllers\DosExamController::class, 'missing'])->name('missing');
});

// DOS — Report Cards + Promote + Teachers
Route::middleware(['auth', 'role:dos|principal'])->prefix('dos')->name('dos.')->group(function () {
    Route::get('/report-cards',       [App\Http\Controllers\DosReportController::class, 'reportCardsIndex'])->name('report-cards.index');
    Route::post('/report-cards/bulk', [App\Http\Controllers\DosReportController::class, 'reportCardsBulk'])->name('report-cards.bulk');

    Route::get('/promote',  [App\Http\Controllers\DosReportController::class, 'promoteIndex'])->name('promote.index');
    Route::post('/promote', [App\Http\Controllers\DosReportController::class, 'promote'])->name('promote.run');

    Route::get('/teachers',  [App\Http\Controllers\DosReportController::class, 'teachersIndex'])->name('teachers.index');
    Route::post('/teachers', [App\Http\Controllers\DosReportController::class, 'teachersStore'])->name('teachers.store');
});

// Bursar Finance
Route::middleware(['auth', 'role:bursar|principal'])->prefix('bursar')->name('bursar.')->group(function () {
    Route::get('/',                    [App\Http\Controllers\BursarController::class, 'dashboard'])->name('dashboard');
    Route::get('/students',            [App\Http\Controllers\BursarController::class, 'students'])->name('students');

    Route::get('/record',              [App\Http\Controllers\BursarController::class, 'recordForm'])->name('record');
    Route::post('/record',             [App\Http\Controllers\BursarController::class, 'record'])->name('record.store');

    Route::get('/payments',            [App\Http\Controllers\BursarController::class, 'payments'])->name('payments');
    Route::get('/receipt/{payment}',   [App\Http\Controllers\BursarController::class, 'receipt'])->name('receipt');
    Route::get('/statement/{student}', [App\Http\Controllers\BursarController::class, 'statement'])->name('statement');
    Route::post('/balance-sms/{student}', [App\Http\Controllers\BursarController::class, 'sendBalance'])->name('balance-sms');

    Route::get('/balances',            [App\Http\Controllers\BursarController::class, 'balances'])->name('balances');
    Route::get('/balances/export',     [App\Http\Controllers\BursarController::class, 'exportBalances'])->name('balances.export');

    Route::get('/daily',               [App\Http\Controllers\BursarController::class, 'daily'])->name('daily');

    Route::get('/bulk',                [App\Http\Controllers\BursarController::class, 'bulkForm'])->name('bulk');
    Route::post('/bulk',               [App\Http\Controllers\BursarController::class, 'bulkUpload'])->name('bulk.upload');
});

// Bursar Finance
Route::middleware(['auth', 'role:bursar|principal'])->prefix('bursar')->name('bursar.')->group(function () {
    Route::get('/',                    [App\Http\Controllers\BursarController::class, 'dashboard'])->name('dashboard');
    Route::get('/students',            [App\Http\Controllers\BursarController::class, 'students'])->name('students');

    Route::get('/record',              [App\Http\Controllers\BursarController::class, 'recordForm'])->name('record');
    Route::post('/record',             [App\Http\Controllers\BursarController::class, 'record'])->name('record.store');

    Route::get('/payments',            [App\Http\Controllers\BursarController::class, 'payments'])->name('payments');
    Route::get('/receipt/{payment}',   [App\Http\Controllers\BursarController::class, 'receipt'])->name('receipt');
    Route::get('/statement/{student}', [App\Http\Controllers\BursarController::class, 'statement'])->name('statement');
    Route::post('/balance-sms/{student}', [App\Http\Controllers\BursarController::class, 'sendBalance'])->name('balance-sms');

    Route::get('/balances',            [App\Http\Controllers\BursarController::class, 'balances'])->name('balances');
    Route::get('/balances/export',     [App\Http\Controllers\BursarController::class, 'exportBalances'])->name('balances.export');

    Route::get('/daily',               [App\Http\Controllers\BursarController::class, 'daily'])->name('daily');

    Route::get('/bulk',                [App\Http\Controllers\BursarController::class, 'bulkForm'])->name('bulk');
    Route::post('/bulk',               [App\Http\Controllers\BursarController::class, 'bulkUpload'])->name('bulk.upload');
});

// Teacher
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/', [App\Http\Controllers\TeacherController::class, 'dashboard'])->name('dashboard');
    Route::get('/classes', [App\Http\Controllers\TeacherController::class, 'classes'])->name('classes');
    Route::get('/timetable', [App\Http\Controllers\TeacherController::class, 'timetable'])->name('timetable');
    Route::get('/marks', [App\Http\Controllers\TeacherController::class, 'marksForm'])->name('marks');
    Route::post('/marks', [App\Http\Controllers\TeacherController::class, 'marksStore'])->name('marks.store');
    Route::get('/homework', [App\Http\Controllers\TeacherController::class, 'homeworkIndex'])->name('homework');
    Route::post('/homework', [App\Http\Controllers\TeacherController::class, 'homeworkStore'])->name('homework.store');
    Route::get('/class-list/pdf', [App\Http\Controllers\TeacherController::class, 'classListPdf'])->name('teacher.classListPdf');
});

// Principal — News / Admissions / Contacts / Expenses
Route::middleware(['auth', 'role:principal'])->prefix('principal')->name('principal.')->group(function () {
    // News
    Route::get('/news',               [App\Http\Controllers\NewsAdminController::class, 'index'])->name('news.index');
    Route::get('/news/create',        [App\Http\Controllers\NewsAdminController::class, 'create'])->name('news.create');
    Route::post('/news',              [App\Http\Controllers\NewsAdminController::class, 'store'])->name('news.store');
    Route::get('/news/{news}/edit',   [App\Http\Controllers\NewsAdminController::class, 'edit'])->name('news.edit');
    Route::put('/news/{news}',        [App\Http\Controllers\NewsAdminController::class, 'update'])->name('news.update');
    Route::delete('/news/{news}',     [App\Http\Controllers\NewsAdminController::class, 'destroy'])->name('news.destroy');

    // Admissions
    Route::get('/admissions',                  [App\Http\Controllers\AdmissionAdminController::class, 'index'])->name('admissions.index');
    Route::get('/admissions/{admission}',      [App\Http\Controllers\AdmissionAdminController::class, 'show'])->name('admissions.show');
    Route::post('/admissions/{admission}/status', [App\Http\Controllers\AdmissionAdminController::class, 'updateStatus'])->name('admissions.status');
    Route::delete('/admissions/{admission}',   [App\Http\Controllers\AdmissionAdminController::class, 'destroy'])->name('admissions.destroy');

    // Contacts
    Route::get('/contacts',                 [App\Http\Controllers\ContactAdminController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/{contact}',       [App\Http\Controllers\ContactAdminController::class, 'show'])->name('contacts.show');
    Route::post('/contacts/{contact}/toggle', [App\Http\Controllers\ContactAdminController::class, 'toggle'])->name('contacts.toggle');
    Route::delete('/contacts/{contact}',    [App\Http\Controllers\ContactAdminController::class, 'destroy'])->name('contacts.destroy');

    // Expenses
    Route::get('/expenses',                 [App\Http\Controllers\ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses',                [App\Http\Controllers\ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{expense}',    [App\Http\Controllers\ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::get('/expenses/export',          [App\Http\Controllers\ExpenseController::class, 'export'])->name('expenses.export');
});

// Public news
Route::get('/news', [App\Http\Controllers\PublicController::class, 'news'])->name('news');
Route::get('/news/{slug}', [App\Http\Controllers\PublicController::class, 'newsShow'])->name('news.show');

// Public timetable
Route::get('/timetable', [App\Http\Controllers\PublicController::class, 'timetable'])->name('timetable');

// Password change (any logged-in user)
Route::middleware('auth')->group(function () {
    Route::get('/password', [App\Http\Controllers\PasswordController::class, 'show'])->name('password.show');
    Route::post('/password', [App\Http\Controllers\PasswordController::class, 'update'])->name('password.update');
});
