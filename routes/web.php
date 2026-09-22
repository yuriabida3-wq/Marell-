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
    Route::get('/class-list/pdf', [App\Http\Controllers\TeacherController::class, 'classListPdf'])->name('classListPdf');
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

// Public receipt verification
Route::get('/verify-receipt/{receipt_no}', [App\Http\Controllers\VerifyController::class, 'receipt'])->name('verify.receipt');

// Principal — Live Collection Projector
Route::middleware(['auth', 'role:principal'])->prefix('principal')->name('principal.')->group(function () {
    Route::get('/live-collection', [App\Http\Controllers\LiveCollectionController::class, 'index'])->name('live-collection');
    Route::get('/live-collection/feed', [App\Http\Controllers\LiveCollectionController::class, 'feed'])->name('live-collection.feed');
});

// Public anonymous report
Route::get('/report', [App\Http\Controllers\ConfessionController::class, 'form'])->name('confession.form');
Route::post('/report', [App\Http\Controllers\ConfessionController::class, 'submit'])->middleware('throttle:5,1')->name('confession.submit');

// Principal inbox
Route::middleware(['auth', 'role:principal'])->prefix('principal')->name('principal.')->group(function () {
    Route::get('/confessions', [App\Http\Controllers\ConfessionController::class, 'index'])->name('confessions.index');
    Route::get('/confessions/{confession}', [App\Http\Controllers\ConfessionController::class, 'show'])->name('confessions.show');
    Route::post('/confessions/{confession}/flag', [App\Http\Controllers\ConfessionController::class, 'flag'])->name('confessions.flag');
    Route::delete('/confessions/{confession}', [App\Http\Controllers\ConfessionController::class, 'destroy'])->name('confessions.destroy');
});

// Principal — Late Fines
Route::middleware(['auth', 'role:principal'])->prefix('principal')->name('principal.')->group(function () {
    Route::get('/fines', [App\Http\Controllers\LateFineController::class, 'index'])->name('fines.index');
    Route::post('/fines/apply', [App\Http\Controllers\LateFineController::class, 'apply'])->name('fines.apply');
    Route::post('/fines/{fine}/waive', [App\Http\Controllers\LateFineController::class, 'waive'])->name('fines.waive');
});

// Principal — AI Predictions
Route::middleware(['auth', 'role:principal'])->prefix('principal')->name('principal.')->group(function () {
    Route::get('/predictions', [App\Http\Controllers\PredictionController::class, 'index'])->name('predictions');
});

// Principal — Board Report
Route::middleware(['auth', 'role:principal'])->prefix('principal')->name('principal.')->group(function () {
    Route::get('/board-report', [App\Http\Controllers\BoardReportController::class, 'index'])->name('board-report');
    Route::post('/board-report', [App\Http\Controllers\BoardReportController::class, 'generate'])->name('board-report.generate');
});

// PWA
Route::get('/manifest.webmanifest', [App\Http\Controllers\PwaController::class, 'manifest'])->name('pwa.manifest');
Route::get('/service-worker.js',    [App\Http\Controllers\PwaController::class, 'serviceWorker']);
Route::get('/icon-{size}.png',      [App\Http\Controllers\PwaController::class, 'icon'])->where('size', '192|512');

// WhatsApp Bot — public webhook (no auth, no CSRF)
Route::get('/webhook/whatsapp',  [App\Http\Controllers\WhatsappBotController::class, 'verify']);
Route::post('/webhook/whatsapp', [App\Http\Controllers\WhatsappBotController::class, 'incoming']);

// Demo tester (only in local/env to preview replies)
Route::get('/whatsapp-demo', function () {
    return view('public.whatsapp-demo');
})->name('whatsapp.demo');

Route::post('/whatsapp-demo', function (\Illuminate\Http\Request $r) {
    $r->validate(['phone' => 'required|string', 'message' => 'required|string']);
    $bot = new \App\Http\Controllers\WhatsappBotController();
    return response()->json([
        'reply' => $bot->handleMessage($r->phone, $r->message),
    ]);
});

// Principal — Voice SMS
Route::middleware(['auth', 'role:principal'])->prefix('principal')->name('principal.')->group(function () {
    Route::get('/voice-sms',         [App\Http\Controllers\VoiceSmsController::class, 'index'])->name('voice-sms');
    Route::post('/voice-sms/bulk',   [App\Http\Controllers\VoiceSmsController::class, 'bulkRemind'])->name('voice-sms.bulk');
    Route::post('/voice-sms/preview',[App\Http\Controllers\VoiceSmsController::class, 'preview'])->name('voice-sms.preview');
});

// DOS — Teacher Subjects
Route::middleware(['auth', 'role:dos|principal'])->prefix('dos')->name('dos.')->group(function () {
    Route::get('/teacher-subjects',                     [App\Http\Controllers\TeacherSubjectController::class, 'index'])->name('teacher-subjects.index');
    Route::post('/teacher-subjects',                    [App\Http\Controllers\TeacherSubjectController::class, 'store'])->name('teacher-subjects.store');
    Route::post('/teacher-subjects/bulk',               [App\Http\Controllers\TeacherSubjectController::class, 'bulkAssign'])->name('teacher-subjects.bulk');
    Route::delete('/teacher-subjects/{teacherSubject}', [App\Http\Controllers\TeacherSubjectController::class, 'destroy'])->name('teacher-subjects.destroy');
    Route::delete('/teacher-subjects/clear/{user}',     [App\Http\Controllers\TeacherSubjectController::class, 'clearTeacher'])->name('teacher-subjects.clear');
});

// DOS — School-Wide Timetable Generator
Route::middleware(['auth', 'role:dos|principal'])->prefix('dos')->name('dos.')->group(function () {
    Route::get('/timetable-generator',        [App\Http\Controllers\TimetableGeneratorController::class, 'index'])->name('timetable-generator.index');
    Route::post('/timetable-generator',       [App\Http\Controllers\TimetableGeneratorController::class, 'generate'])->name('timetable-generator.generate');
    Route::post('/timetable-generator/verify',[App\Http\Controllers\TimetableGeneratorController::class, 'verify'])->name('timetable-generator.verify');
});

// DOS — Exam Controls (per-class marks portal)
Route::middleware(['auth', 'role:dos|principal'])->prefix('dos')->name('dos.')->group(function () {
    Route::get('/exams/{exam}/controls',            [App\Http\Controllers\ExamControlController::class, 'index'])->name('exams.controls');
    Route::post('/exams/{exam}/controls/open',      [App\Http\Controllers\ExamControlController::class, 'open'])->name('exams.controls.open');
    Route::post('/exams/{exam}/controls/close',     [App\Http\Controllers\ExamControlController::class, 'close'])->name('exams.controls.close');
    Route::post('/exams/{exam}/controls/open-all',  [App\Http\Controllers\ExamControlController::class, 'openAll'])->name('exams.controls.open-all');
    Route::post('/exams/{exam}/controls/close-all', [App\Http\Controllers\ExamControlController::class, 'closeAll'])->name('exams.controls.close-all');
});

// Principal — Audit Log
Route::middleware(['auth', 'role:principal'])->prefix('principal')->name('principal.')->group(function () {
    Route::get('/audit', [App\Http\Controllers\AuditLogController::class, 'index'])->name('audit.index');
});

// Virtual Assistant (public, throttled)
Route::post('/assistant/ask', [App\Http\Controllers\AssistantController::class, 'ask'])
    ->middleware('throttle:30,1')
    ->name('assistant.ask');

// DOS — Student Management
Route::middleware(['auth', 'role:dos|principal'])->prefix('dos')->name('dos.')->group(function () {
    Route::get('/students',                    [App\Http\Controllers\DosStudentController::class, 'index'])->name('students.index');
    Route::get('/students/create',             [App\Http\Controllers\DosStudentController::class, 'create'])->name('students.create');
    Route::post('/students',                   [App\Http\Controllers\DosStudentController::class, 'store'])->name('students.store');
    Route::get('/students/{student}',          [App\Http\Controllers\DosStudentController::class, 'show'])->name('students.show');
    Route::get('/students/{student}/edit',     [App\Http\Controllers\DosStudentController::class, 'edit'])->name('students.edit');
    Route::put('/students/{student}',          [App\Http\Controllers\DosStudentController::class, 'update'])->name('students.update');
});

// Principal — Emergency Broadcast
Route::middleware(['auth', 'role:principal'])->prefix('principal')->name('principal.')->group(function () {
    Route::get('/emergency',              [App\Http\Controllers\EmergencyAlertController::class, 'index'])->name('emergency.index');
    Route::get('/emergency/create',       [App\Http\Controllers\EmergencyAlertController::class, 'create'])->name('emergency.create');
    Route::post('/emergency',             [App\Http\Controllers\EmergencyAlertController::class, 'store'])->name('emergency.store');
    Route::get('/emergency/{emergency}',  [App\Http\Controllers\EmergencyAlertController::class, 'show'])->name('emergency.show');
    Route::delete('/emergency/{emergency}',[App\Http\Controllers\EmergencyAlertController::class, 'destroy'])->name('emergency.destroy');
});

// Library (DOS + Principal + Bursar can all access)
Route::middleware(['auth', 'role:dos|principal|bursar|teacher'])->prefix('library')->name('library.')->group(function () {
    Route::get('/',                         [App\Http\Controllers\LibraryController::class, 'index'])->name('index');

    // Books
    Route::get('/books',                    [App\Http\Controllers\LibraryController::class, 'books'])->name('books');
    Route::get('/books/create',             [App\Http\Controllers\LibraryController::class, 'createBook'])->name('books.create');
    Route::post('/books',                   [App\Http\Controllers\LibraryController::class, 'storeBook'])->name('books.store');
    Route::get('/books/{book}/edit',        [App\Http\Controllers\LibraryController::class, 'editBook'])->name('books.edit');
    Route::put('/books/{book}',             [App\Http\Controllers\LibraryController::class, 'updateBook'])->name('books.update');

    // Loans
    Route::get('/loans',                    [App\Http\Controllers\LibraryController::class, 'loans'])->name('loans');
    Route::post('/loans/{loan}/return',     [App\Http\Controllers\LibraryController::class, 'returnBook'])->name('loans.return');
    Route::post('/loans/{loan}/fine-paid',  [App\Http\Controllers\LibraryController::class, 'markFinePaid'])->name('loans.fine-paid');

    // Issue / Return
    Route::get('/issue',                    [App\Http\Controllers\LibraryController::class, 'issueForm'])->name('issue');
    Route::post('/issue',                   [App\Http\Controllers\LibraryController::class, 'issue'])->name('issue.store');
    Route::get('/return',                   [App\Http\Controllers\LibraryController::class, 'returnForm'])->name('return');
});

// Visitor Management (gate staff + principal + dos)
Route::middleware(['auth', 'role:principal|dos|bursar|teacher'])->prefix('visitors')->name('visitors.')->group(function () {
    Route::get('/gate',                     [App\Http\Controllers\VisitorController::class, 'gate'])->name('gate');
    Route::get('/',                         [App\Http\Controllers\VisitorController::class, 'index'])->name('index');
    Route::get('/check-in',                 [App\Http\Controllers\VisitorController::class, 'checkInForm'])->name('check-in');
    Route::post('/check-in',                [App\Http\Controllers\VisitorController::class, 'checkIn'])->name('check-in.store');
    Route::post('/check-out/{visitor}',     [App\Http\Controllers\VisitorController::class, 'checkOut'])->name('check-out');
    Route::get('/pre-register',             [App\Http\Controllers\VisitorController::class, 'preRegisterForm'])->name('pre-register');
    Route::post('/pre-register',            [App\Http\Controllers\VisitorController::class, 'preRegister'])->name('pre-register.store');
    Route::get('/{visitor}',                [App\Http\Controllers\VisitorController::class, 'show'])->name('show');
    Route::delete('/{visitor}',             [App\Http\Controllers\VisitorController::class, 'destroy'])->name('destroy');
});

// Principal — Results Overview
Route::middleware(['auth', 'role:principal'])->prefix('principal')->name('principal.')->group(function () {
    Route::get('/results',                    [App\Http\Controllers\PrincipalResultsController::class, 'index'])->name('results');
    Route::get('/results/export/{exam}',      [App\Http\Controllers\PrincipalResultsController::class, 'exportExam'])->name('results.export');
});

// Principal — Teachers management
Route::middleware(['auth', 'role:principal'])->prefix('principal')->name('principal.')->group(function () {
    Route::get('/teachers',                        [App\Http\Controllers\PrincipalTeacherController::class, 'index'])->name('teachers.index');
    Route::get('/teachers/create',                 [App\Http\Controllers\PrincipalTeacherController::class, 'create'])->name('teachers.create');
    Route::post('/teachers',                       [App\Http\Controllers\PrincipalTeacherController::class, 'store'])->name('teachers.store');
    Route::get('/teachers/{teacher}',              [App\Http\Controllers\PrincipalTeacherController::class, 'show'])->name('teachers.show');
    Route::get('/teachers/{teacher}/edit',         [App\Http\Controllers\PrincipalTeacherController::class, 'edit'])->name('teachers.edit');
    Route::put('/teachers/{teacher}',              [App\Http\Controllers\PrincipalTeacherController::class, 'update'])->name('teachers.update');
    Route::post('/teachers/{teacher}/reset-password', [App\Http\Controllers\PrincipalTeacherController::class, 'resetPassword'])->name('teachers.reset-password');
});

// QR Receipt Scanner (Director, Bursar, DOS)
Route::middleware(['auth', 'role:principal|bursar|dos'])->prefix('qr-scanner')->name('qr.')->group(function () {
    Route::get('/',        [App\Http\Controllers\QrScannerController::class, 'index'])->name('scanner');
    Route::post('/verify', [App\Http\Controllers\QrScannerController::class, 'verify'])->name('verify');
});

// Parent — Approved Pickups (OTP-protected via session)
Route::middleware('parent.auth')->prefix('parent/pickups')->name('parent.pickups')->group(function () {
    Route::get('/',                    [App\Http\Controllers\ParentPickupController::class, 'index']);
    Route::post('/',                   [App\Http\Controllers\ParentPickupController::class, 'store'])->name('.store');
    Route::get('/{pickup}/qr',         [App\Http\Controllers\ParentPickupController::class, 'qrCard'])->name('.qr');
    Route::post('/{pickup}/toggle',    [App\Http\Controllers\ParentPickupController::class, 'toggle'])->name('.toggle');
    Route::delete('/{pickup}',         [App\Http\Controllers\ParentPickupController::class, 'destroy'])->name('.destroy');
});

// Security Guard — Public gate login (no auth guard, session-based)
Route::prefix('security')->name('security.')->group(function () {
    Route::get('/login',   [App\Http\Controllers\SecurityGuardController::class, 'loginForm'])->name('login');
    Route::post('/login',  [App\Http\Controllers\SecurityGuardController::class, 'login'])->name('login.post')->middleware('throttle:10,1');
    Route::post('/logout', [App\Http\Controllers\SecurityGuardController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [App\Http\Controllers\SecurityGuardController::class, 'dashboard'])->name('dashboard');
    Route::get('/scan',      [App\Http\Controllers\SecurityGuardController::class, 'scan'])->name('scan');
    Route::post('/verify',   [App\Http\Controllers\SecurityGuardController::class, 'verify'])->name('verify');
    Route::post('/panic',    [App\Http\Controllers\SecurityGuardController::class, 'panic'])->name('panic')->middleware('throttle:5,1');
    Route::get('/logs',      [App\Http\Controllers\SecurityGuardController::class, 'logs'])->name('logs');
});

// Parent — Library view (OTP protected)
Route::middleware('parent.auth')->prefix('parent/library')->name('parent.library')->group(function () {
    Route::get('/', [App\Http\Controllers\LibraryController::class, 'parentView']);
});

// Public library catalog (no login needed)
Route::get('/library-catalog', [App\Http\Controllers\LibraryController::class, 'publicCatalog'])->name('library.public');

// ============ STUDENT QR ============
Route::get('/student-qr/{token}', [App\Http\Controllers\StudentQrController::class, 'show'])->name('student.qr.public');

// ============ CANTEEN (Guard or Admin) ============
Route::prefix('canteen')->name('canteen.')->group(function () {
    Route::get('/',              [App\Http\Controllers\WalletController::class, 'canteen'])->name('index');
    Route::get('/logs',          [App\Http\Controllers\WalletController::class, 'canteenLogs'])->name('logs');
    Route::post('/lookup',       [App\Http\Controllers\WalletController::class, 'canteenLookup'])->name('lookup');
    Route::post('/sell',         [App\Http\Controllers\WalletController::class, 'canteenSell'])->name('sell');
});

// ============ PARENT WALLET (OTP protected) ============
Route::middleware('parent.auth')->prefix('parent/wallet')->name('parent.wallet')->group(function () {
    Route::get('/',              [App\Http\Controllers\WalletController::class, 'parentIndex']);
    Route::post('/topup',        [App\Http\Controllers\WalletController::class, 'parentTopUp'])->name('.topup');
    Route::post('/settings',     [App\Http\Controllers\WalletController::class, 'parentSettings'])->name('.settings');
    Route::get('/{student}/qr',  [App\Http\Controllers\StudentQrController::class, 'parentCard'])->name('.qr');
});

// ============ ADMIN WALLET ============
Route::middleware(['auth', 'role:principal|bursar'])->prefix('principal/wallet')->name('principal.wallet.')->group(function () {
    Route::get('/',              [App\Http\Controllers\WalletController::class, 'admin'])->name('index');
    Route::post('/load',         [App\Http\Controllers\WalletController::class, 'adminLoad'])->name('load');
    Route::get('/search',        [App\Http\Controllers\WalletController::class, 'adminSearch'])->name('search');
});

// ============ STUDENT QR CARDS (admin print) ============
Route::middleware(['auth', 'role:principal|dos|bursar'])->prefix('student-qr-cards')->name('student-qr-cards.')->group(function () {
    Route::get('/{student}',     [App\Http\Controllers\StudentQrController::class, 'card'])->name('card');
    Route::post('/bulk-pdf',     [App\Http\Controllers\StudentQrController::class, 'bulkPdf'])->name('bulk-pdf');
});

// Teacher — Attendance
Route::middleware(['auth', 'role:teacher'])->prefix('teacher/attendance')->name('teacher.attendance.')->group(function () {
    Route::get('/',         [App\Http\Controllers\AttendanceController::class, 'markForm'])->name('mark');
    Route::post('/',        [App\Http\Controllers\AttendanceController::class, 'markStore'])->name('store');
});

// Parent — Excuse attendance
Route::middleware('parent.auth')->post('/parent/attendance/excuse', [App\Http\Controllers\AttendanceController::class, 'parentExcuse'])->name('parent.attendance.excuse');

// Principal + DOS — Attendance dashboard
Route::middleware(['auth', 'role:principal|dos'])->prefix('principal/attendance')->name('principal.attendance.')->group(function () {
    Route::get('/',              [App\Http\Controllers\AttendanceController::class, 'index'])->name('index');
    Route::get('/chronic',       [App\Http\Controllers\AttendanceController::class, 'chronic'])->name('chronic');
    Route::get('/export',        [App\Http\Controllers\AttendanceController::class, 'export'])->name('export');
});

// Principal — Fee Autopilot
Route::middleware(['auth', 'role:principal'])->prefix('principal/fee-autopilot')->name('principal.fee-autopilot.')->group(function () {
    Route::get('/',                    [App\Http\Controllers\FeeAutopilotController::class, 'index'])->name('index');
    Route::post('/run',                [App\Http\Controllers\FeeAutopilotController::class, 'run'])->name('run');
    Route::get('/settings',            [App\Http\Controllers\FeeAutopilotController::class, 'settings'])->name('settings');
    Route::post('/settings',           [App\Http\Controllers\FeeAutopilotController::class, 'updateSettings'])->name('settings.update');
    Route::get('/student/{student}',   [App\Http\Controllers\FeeAutopilotController::class, 'student'])->name('student');
    Route::post('/student/{student}/trigger', [App\Http\Controllers\FeeAutopilotController::class, 'triggerNext'])->name('trigger');
});

// Teacher — Lesson Plans + Check-in + Performance
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::post('/clock-in',                       [App\Http\Controllers\TeacherAccountabilityController::class, 'clockIn'])->name('clock-in');
    Route::post('/clock-out',                      [App\Http\Controllers\TeacherAccountabilityController::class, 'clockOut'])->name('clock-out');
    Route::get('/lesson-plans',                    [App\Http\Controllers\TeacherAccountabilityController::class, 'myLessonPlans'])->name('lesson-plans.index');
    Route::post('/lesson-plans',                   [App\Http\Controllers\TeacherAccountabilityController::class, 'submitLessonPlan'])->name('lesson-plans.store');
    Route::get('/performance',                     [App\Http\Controllers\TeacherAccountabilityController::class, 'myPerformance'])->name('performance');
});

// DOS — Lesson Plan Reviews + Teacher Performance
Route::middleware(['auth', 'role:dos|principal'])->prefix('dos')->name('dos.')->group(function () {
    Route::get('/lesson-plans',                    [App\Http\Controllers\TeacherAccountabilityController::class, 'reviewIndex'])->name('lesson-plans.index');
    Route::post('/lesson-plans/{plan}/review',     [App\Http\Controllers\TeacherAccountabilityController::class, 'review'])->name('lesson-plans.review');
    Route::get('/teacher-performance',             [App\Http\Controllers\TeacherAccountabilityController::class, 'performanceIndex'])->name('teacher-performance.index');
    Route::post('/teacher-performance/compute',    [App\Http\Controllers\TeacherAccountabilityController::class, 'computeAll'])->name('teacher-performance.compute');
});
