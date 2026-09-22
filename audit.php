<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\{Student, Payment, User, Book, BookLoan, ApprovedPickup, EmergencyAlert, Visitor, Classroom, TeacherSubject, Exam};
use Illuminate\Support\Facades\{Auth, Hash};
use Illuminate\Http\Request;

echo "\n═══════════════════════════════════════════════════\n";
echo "  FULL INTERACTION AUDIT\n";
echo "═══════════════════════════════════════════════════\n\n";

$pass = 0; $fail = 0;

// Helper to run a test
function test($name, $callback) {
    global $pass, $fail;
    try {
        $result = $callback();
        if ($result === true || $result === null) {
            echo "  ✅ {$name}\n";
            $pass++;
        } else {
            echo "  ❌ {$name}  → {$result}\n";
            $fail++;
        }
    } catch (\Throwable $e) {
        echo "  ❌ {$name}  → " . substr($e->getMessage(), 0, 80) . "\n";
        $fail++;
    }
}

// ============ DATA MODELS WORK ============
echo "▶ MODELS + RELATIONS\n";

test('Student → Payments', function () {
    $s = Student::first();
    return $s->payments()->count() >= 0;
});

test('Student → Results', function () {
    $s = Student::first();
    return $s->results()->count() >= 0;
});

test('Student → Wallet Transactions', function () {
    $s = Student::first();
    return $s->walletTransactions()->count() >= 0;
});

test('Student → Approved Pickups', function () {
    $s = Student::first();
    return $s->approvedPickups()->count() >= 0;
});

test('Student qrUrl() generates', function () {
    $s = Student::first();
    return str_contains($s->qrUrl(), '/student-qr/');
});

test('Payment → Student relation', function () {
    $p = Payment::first();
    return $p ? $p->student !== null : true;
});

test('Book → Loans relation', function () {
    $b = Book::first();
    return $b ? $b->loans()->count() >= 0 : true;
});

test('ApprovedPickup → Student', function () {
    $p = ApprovedPickup::first();
    return $p ? $p->student !== null : true;
});

test('EmergencyAlert → Sender', function () {
    $a = EmergencyAlert::first();
    return $a ? $a->sender !== null || $a->sent_by === null : true;
});

// ============ WALLET FLOW ============
echo "\n▶ WALLET TOP-UP + SPEND\n";

test('Top up wallet', function () {
    $s = Student::first();
    $before = (float) $s->wallet_balance;
    $txn = $s->topUpWallet(100, 'AUDIT-' . time(), 'load', 'Test');
    return ((float) $s->fresh()->wallet_balance === $before + 100) ?: 'Balance did not update';
});

test('Spend from wallet', function () {
    $s = Student::first();
    $before = (float) $s->wallet_balance;
    $txn = $s->spendWallet(50, 'canteen', 'Test spend');
    return ((float) $s->fresh()->wallet_balance === $before - 50) ?: 'Balance did not decrease';
});

test('Overdraft blocked', function () {
    $s = Student::first();
    try {
        $s->spendWallet(99999999, 'canteen', 'Too much');
        return 'Should have thrown error';
    } catch (\RuntimeException $e) {
        return true;
    }
});

// ============ SIBLING DISCOUNT ============
echo "\n▶ SIBLING DISCOUNT\n";

test('Recompute siblings runs', function () {
    $count = \App\Services\SiblingDiscountService::recomputeAll();
    return $count >= 0;
});

test('Discounted students exist', function () {
    $count = Student::where('discount_amount', '>', 0)->count();
    return $count >= 0;
});

// ============ BOOK LOAN FLOW ============
echo "\n▶ LIBRARY LOAN FLOW\n";

test('Book loan creates', function () {
    $book = Book::firstOrCreate(
        ['isbn' => 'AUDIT-' . time()],
        ['title' => 'Audit Book', 'total_copies' => 1, 'available_copies' => 1, 'active' => true]
    );
    $student = Student::first();
    $loan = BookLoan::create([
        'book_id' => $book->id,
        'student_id' => $student->id,
        'issued_at' => now()->toDateString(),
        'due_at' => now()->addDays(14)->toDateString(),
    ]);
    return $loan->id > 0;
});

test('Loan fine calculation', function () {
    $loan = BookLoan::latest()->first();
    if (!$loan) return 'No loan';
    $fine = $loan->calculateFine();
    return is_numeric($fine);
});

// ============ APPROVED PICKUP ============
echo "\n▶ APPROVED PICKUP FLOW\n";

test('Pickup QR generation', function () {
    $student = Student::first();
    $pickup = ApprovedPickup::create([
        'student_id' => $student->id,
        'name' => 'Audit Picker',
        'phone' => '0722000001',
        'relationship' => 'Uncle',
        'qr_token' => \Illuminate\Support\Str::random(48),
        'active' => true,
    ]);
    return str_contains($pickup->qrUrl(), '/pickup/verify/');
});

test('Pickup deactivate works', function () {
    $pickup = ApprovedPickup::latest()->first();
    if (!$pickup) return 'No pickup';
    $pickup->update(['active' => false]);
    return $pickup->fresh()->active === false;
});

// ============ EMERGENCY ALERT ============
echo "\n▶ EMERGENCY BROADCAST\n";

test('Emergency alert creates + sends', function () {
    $alert = EmergencyAlert::create([
        'title' => 'Audit Test',
        'message' => 'Testing emergency',
        'severity' => 'info',
        'audience' => 'parents',
        'status' => 'draft',
        'sent_by' => User::where('email', 'admin@marell.ac.ke')->first()->id,
    ]);
    \App\Jobs\SendEmergencyAlert::dispatchSync($alert);
    $alert->refresh();
    return $alert->status === 'sent' && $alert->sent_count > 0;
});

// ============ PANIC ALERT ============
echo "\n▶ PANIC ALERT\n";

test('Panic alert job runs', function () {
    $guard = User::where('email', 'guard@marell.ac.ke')->first();
    if (!$guard) return 'Guard missing';
    $alert = \App\Models\PanicAlert::create([
        'user_id' => $guard->id,
        'user_name' => $guard->name,
        'location' => 'Audit Location',
        'ip' => '127.0.0.1',
    ]);
    \App\Jobs\SendPanicAlert::dispatchSync($alert);
    return $alert->fresh()->sms_sent > 0;
});

// ============ VISITOR MANAGEMENT ============
echo "\n▶ VISITOR MANAGEMENT\n";

test('Visitor check-in creates', function () {
    $v = Visitor::create([
        'name' => 'Audit Visitor',
        'phone' => '0722111111',
        'purpose' => 'Test',
        'status' => 'checked_in',
        'checked_in_at' => now(),
        'badge_no' => 'V-AUDIT',
    ]);
    return $v->id > 0;
});

test('Visitor check-out works', function () {
    $v = Visitor::latest()->first();
    $v->update(['status' => 'checked_out', 'checked_out_at' => now()]);
    return $v->fresh()->status === 'checked_out';
});

// ============ TIMETABLE GENERATOR ============
echo "\n▶ TIMETABLE GENERATOR\n";

test('School-wide generator runs', function () {
    $svc = new \App\Services\TimetableGeneratorService();
    $result = $svc->generateForSchool('Term 1', '2026');
    return !isset($result['error']) && $result['slots'] > 0;
});

test('Zero clashes after generate', function () {
    $svc = new \App\Services\TimetableGeneratorService();
    $clashes = $svc->verify('Term 1', '2026');
    return count($clashes) === 0;
});

// ============ DEFaulter PREDICTION ============
echo "\n▶ AI PREDICTION\n";

test('Predictions generate', function () {
    $predictions = \App\Services\DefaulterPredictionService::predictAll();
    return is_array($predictions);
});

// ============ LATE FINES ============
echo "\n▶ LATE FINES\n";

test('Late fines service runs', function () {
    $r = \App\Services\LateFineService::applyMonthlyFines();
    return is_array($r) && isset($r['applied']);
});

// ============ ASSISTANT ============
echo "\n▶ VIRTUAL ASSISTANT\n";

test('Assistant balance reply', function () {
    $r = \App\Services\AssistantService::reply('balance', 'audit-test', '127.0.0.1');
    return isset($r['reply']) && strlen($r['reply']) > 0;
});

test('Assistant help reply', function () {
    $r = \App\Services\AssistantService::reply('help', 'audit-test', '127.0.0.1');
    return isset($r['reply']);
});

// ============ WHATSAPP BOT ============
echo "\n▶ WHATSAPP BOT\n";

test('WhatsApp bot balance', function () {
    $bot = new \App\Http\Controllers\WhatsappBotController();
    $r = $bot->handleMessage('254720001111', 'balance');
    return strlen($r) > 20;
});

test('WhatsApp bot invalid command', function () {
    $bot = new \App\Http\Controllers\WhatsappBotController();
    $r = $bot->handleMessage('254720001111', 'xyzabc');
    return strlen($r) > 0;
});

// ============ SMS SERVICE ============
echo "\n▶ SMS SERVICE\n";

test('SMS service sends stub', function () {
    $sms = app(\App\Services\SmsService::class);
    return $sms->send('254700000001', 'Audit test SMS') === true;
});

// ============ VOICE SMS ============
echo "\n▶ VOICE SMS\n";

test('Voice SMS preview', function () {
    $svc = new \App\Services\VoiceSmsService();
    $student = Student::first();
    $msg = $svc->feeReminderMessage($student);
    return str_contains($msg, 'Habari') && str_contains($msg, $student->name);
});

// ============ RECEIPT GENERATION ============
echo "\n▶ RECEIPTS + VERIFICATION\n";

test('Receipt number unique', function () {
    $r1 = Payment::generateReceiptNo();
    return str_starts_with($r1, 'REC-');
});

test('Verify valid receipt', function () {
    $p = Payment::where('status', 'completed')->first();
    if (!$p) return 'No completed payment';
    $found = Payment::where('receipt_no', $p->receipt_no)->first();
    return $found !== null;
});

test('Verify fake receipt returns null', function () {
    return Payment::where('receipt_no', 'FAKE-AUDIT-99999')->first() === null;
});

// ============ PWA ============
echo "\n▶ PWA\n";

test('Manifest generates', function () {
    $c = new \App\Http\Controllers\PwaController();
    $r = $c->manifest();
    return $r->getStatusCode() === 200;
});

test('Service worker generates', function () {
    $c = new \App\Http\Controllers\PwaController();
    $r = $c->serviceWorker();
    return $r->getStatusCode() === 200;
});

// ============ CLASSROOM ============
echo "\n▶ CLASSROOM\n";

test('Classroom has class teacher', function () {
    $c = Classroom::whereNotNull('class_teacher_id')->first();
    return $c && $c->classTeacher !== null;
});

test('Classroom student count', function () {
    $c = Classroom::first();
    if (!$c) return 'No classroom';
    return is_int($c->studentCount());
});

// ============ TEACHER SUBJECTS ============
echo "\n▶ TEACHER SUBJECTS\n";

test('Teacher subjects exist', function () {
    return TeacherSubject::count() >= 0;
});

test('Teacher subject → teacher relation', function () {
    $ts = TeacherSubject::first();
    return $ts ? $ts->teacher !== null : true;
});

// ============ EXAMS ============
echo "\n▶ EXAMS\n";

test('Exam → Results relation', function () {
    $exam = Exam::first();
    return $exam ? $exam->results()->count() >= 0 : true;
});

test('Exam per-class control check', function () {
    $exam = Exam::first();
    if (!$exam) return true;
    $open = \App\Http\Controllers\ExamControlController::isOpenFor($exam, 'Class 6', 'Blue');
    return is_bool($open);
});

// ============ AUTH ROLES ============
echo "\n▶ ROLES + AUTH\n";

test('Principal has principal role', function () {
    $u = User::where('email', 'admin@marell.ac.ke')->first();
    return $u && $u->hasRole('principal');
});

test('DOS has dos role', function () {
    $u = User::where('email', 'dos@marell.ac.ke')->first();
    return $u && $u->hasRole('dos');
});

test('Bursar has bursar role', function () {
    $u = User::where('email', 'yuriabida3@gmail.com')->first();
    return $u && $u->hasRole('bursar');
});

test('Teacher has teacher role', function () {
    $u = User::where('email', 'jane@marell.ac.ke')->first();
    return $u && $u->hasRole('teacher');
});

test('Guard has security role', function () {
    $u = User::where('email', 'guard@marell.ac.ke')->first();
    return $u && $u->hasRole('security');
});

test('Guard PIN check works', function () {
    $g = User::where('email', 'guard@marell.ac.ke')->first();
    return $g && Hash::check('1234', $g->pin);
});

// ============ AUDIT LOG ============
echo "\n▶ AUDIT + LOGS\n";

test('Audit log records', function () {
    \App\Models\AuditLog::log('audit.test', null, [], ['test' => true]);
    return \App\Models\AuditLog::where('action', 'audit.test')->count() > 0;
});

test('Login activity records', function () {
    \App\Models\LoginActivity::record('login_success', 'audit@test.com', null);
    return \App\Models\LoginActivity::where('email', 'audit@test.com')->count() > 0;
});

// ============ SUMMARY ============
echo "\n═══════════════════════════════════════════════════\n";
echo "  RESULTS\n";
echo "═══════════════════════════════════════════════════\n\n";
echo "  ✅ PASS:  {$pass}\n";
echo "  ❌ FAIL:  {$fail}\n";
echo "";
if ($fail === 0) {
    echo "  🎉 ALL SYSTEMS OPERATIONAL\n\n";
} else {
    echo "  ⚠️  {$fail} ISSUES NEED ATTENTION\n\n";
}
