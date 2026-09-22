<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Student;
use App\Models\User;
use App\Models\CanteenItem;
use App\Models\WalletTransaction;
use App\Models\StaffAttendance;
use App\Models\Visitor;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

echo "\n═══════════════════════════════════════════════════\n";
echo "  DEMO SEED SCRIPT — Kenyan School Snapshot\n";
echo "═══════════════════════════════════════════════════\n\n";

// ─── 1. VERIFY WE'RE IN A SAFE ENVIRONMENT ───
if (env('APP_ENV') === 'production' && !file_exists('.demo_seed_confirm')) {
    die("❌ Refusing to seed on production. Create .demo_seed_confirm to override.\n");
}

DB::beginTransaction();
try {

// ─── 2. STAFF USERS ───
echo "Creating staff...\n";
$staffData = [
    ['name' => 'Grace Wanjiku', 'email' => 'admin@marell.ac.ke',  'role' => 'principal'],
    ['name' => 'James Otieno',  'email' => 'dos@marell.ac.ke',    'role' => 'dos'],
    ['name' => 'Mary Njeri',    'email' => 'bursar@marell.ac.ke', 'role' => 'bursar'],
];
foreach ($staffData as $s) {
    $u = User::firstOrCreate(['email' => $s['email']], [
        'name' => $s['name'], 'password' => Hash::make('password'),
        'email_verified_at' => now(),
    ]);
    if (method_exists($u, 'assignRole') && !$u->hasRole($s['role'])) $u->assignRole($s['role']);
}

// 6 Teachers
$teachers = ['Alice Kamau','Peter Mwangi','Faith Achieng','David Kipchoge','Sarah Wanjala','John Kariuki'];
foreach ($teachers as $i => $name) {
    $email = 'teacher' . ($i + 1) . '@marell.ac.ke';
    $u = User::firstOrCreate(['email' => $email], [
        'name' => $name, 'password' => Hash::make('password'),
        'email_verified_at' => now(),
    ]);
    if (method_exists($u, 'assignRole') && !$u->hasRole('teacher')) $u->assignRole('teacher');
}

// 2 Security guards
foreach (['Michael Ochieng','Ruth Chebet'] as $i => $name) {
    $email = 'guard' . ($i + 1) . '@marell.ac.ke';
    $u = User::firstOrCreate(['email' => $email], [
        'name' => $name, 'password' => Hash::make('password'),
        'email_verified_at' => now(),
    ]);
    if (method_exists($u, 'assignRole') && !$u->hasRole('security')) $u->assignRole('security');
}

echo "  ✅ Staff created\n";

// ─── 3. STUDENTS (50) ───
echo "Creating 50 students...\n";
$firstNames = ['Brian','Faith','Kevin','Mercy','Samuel','Grace','Dennis','Cynthia','Collins','Winnie',
               'Elvis','Purity','Isaac','Naomi','Victor','Brenda','Anthony','Esther','Felix','Joy',
               'Mark','Diana','Philip','Ruth','Emmanuel','Linda','Joseph','Rose','Patrick','Irene',
               'Alex','Nancy','Eric','Beatrice','Felix','Jane','Isaac','Caroline','Simon','Emily',
               'Fred','Alice','Godfrey','Lydia','Henry','Mary','Isaiah','Rebecca','Jack','Martha'];
$lastNames = ['Otieno','Wanjiku','Kipchoge','Achieng','Mwangi','Kamau','Njeri','Odhiambo','Kariuki','Nekesa'];
$classes = ['PP1','PP2','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','Grade 7','Grade 8'];
$streams = ['A','B','C'];

$studentIds = [];
for ($i = 0; $i < 50; $i++) {
    $first = $firstNames[$i];
    $last = $lastNames[array_rand($lastNames)];
    $class = $classes[array_rand($classes)];
    $stream = $streams[array_rand($streams)];
    $adm = 'MRL' . str_pad((string)(1000 + $i), 4, '0', STR_PAD_LEFT);
    $phone = '2547' . str_pad((string)rand(10000000, 99999999), 8, '0', STR_PAD_LEFT);
    $totalFee = rand(15000, 45000);
    $paid = rand(0, $totalFee);

    $student = Student::updateOrCreate(
        ['adm_no' => $adm],
        [
            'name' => "$first $last",
            'class' => $class,
            'stream' => $stream,
            'parent_name' => 'Mr/Mrs ' . $last,
            'parent_phone' => $phone,
            'total_fee' => $totalFee,
            'paid_amount' => $paid,
            'balance' => $totalFee - $paid,
            'wallet_balance' => rand(0, 2000),
            'daily_limit' => rand(0, 300),
            'low_balance_alert' => rand(100, 500),
            'status' => 'active',
            'qr_token' => \Illuminate\Support\Str::random(32),
        ]
    );
    $studentIds[] = $student->id;
}
echo "  ✅ 50 students created\n";

// ─── 4. FEE PAYMENTS (100) ───
echo "Creating 100 fee payments...\n";
$exam = null;
for ($i = 0; $i < 100; $i++) {
    $sid = $studentIds[array_rand($studentIds)];
    $amt = rand(500, 8000);
    Payment::create([
        'student_id' => $sid,
        'amount' => $amt,
        'method' => ['M-Pesa','Bank','Cash'][array_rand(['M-Pesa','Bank','Cash'])],
        'transaction_code' => strtoupper(\Illuminate\Support\Str::random(10)),
        'status' => 'completed',
        'receipt_no' => 'RCP' . str_pad((string)$i, 6, '0', STR_PAD_LEFT),
        'term' => 'Term 1',
        'year' => 2026,
        'notes' => 'Demo payment',
    ]);
}
echo "  ✅ 100 payments created\n";

// ─── 5. CANTEEN SALES (200) ───
echo "Creating 200 canteen sales...\n";
$canteenItems = CanteenItem::where('active', true)->get();
if ($canteenItems->count() > 0) {
    for ($i = 0; $i < 200; $i++) {
        $sid = $studentIds[array_rand($studentIds)];
        $item = $canteenItems->random();
        $student = Student::find($sid);
        if ($student->wallet_balance < $item->price) continue;

        WalletTransaction::create([
            'student_id' => $sid,
            'type' => 'spend',
            'amount' => $item->price,
            'balance_after' => $student->wallet_balance - $item->price,
            'category' => 'canteen',
            'status' => 'completed',
            'description' => '1x ' . $item->name,
            'recorded_by' => 'Demo Seed',
            'reference' => 'CANT-' . str_pad((string)$i, 6, '0', STR_PAD_LEFT),
        ]);
    }
}
echo "  ✅ 200 canteen sales created\n";

// ─── 6. STAFF ATTENDANCE (30 days) ───
echo "Creating staff attendance logs...\n";
$staff = User::whereHas('roles', fn($q) => $q->whereIn('name', ['teacher','dos','principal']))->get();
$attendanceCount = 0;
for ($d = 30; $d >= 0; $d--) {
    $date = now()->subDays($d)->setTime(7, rand(0, 45), 0);
    if ($date->isWeekend()) continue;
    foreach ($staff as $s) {
        StaffAttendance::create([
            'user_id' => $s->id,
            'action' => 'in',
            'scanned_at' => $date->copy(),
        ]);
        StaffAttendance::create([
            'user_id' => $s->id,
            'action' => 'out',
            'scanned_at' => $date->copy()->setTime(16, rand(0, 30), 0),
        ]);
        $attendanceCount += 2;
    }
}
echo "  ✅ $attendanceCount staff attendance logs created\n";

// ─── 7. VISITORS (15, some currently inside) ───
echo "Creating visitors...\n";
$purposes = ['Fee payment','Meeting principal','Delivering supplies','Parent-teacher meeting','Maintenance work'];
$visitorNames = ['Mr. Kariuki','Mrs. Wekesa','Mr. Muthoni','Mrs. Otieno','Mr. Mwangi','Mrs. Kilonzo','Mr. Njoroge','Mrs. Auma','Mr. Barasa','Mrs. Wambui','Mr. Ochieng','Mrs. Mueni','Mr. Kiprop','Mrs. Sakwa','Mr. Omondi'];
for ($i = 0; $i < 15; $i++) {
    $inside = $i < 5;
    Visitor::create([
        'name' => $visitorNames[$i],
        'phone' => '2547' . str_pad((string)rand(10000000, 99999999), 8, '0', STR_PAD_LEFT),
        'purpose' => $purposes[array_rand($purposes)],
        'host_name' => 'Principal',
        'host_type' => 'staff',
        'photo' => null,
        'status' => $inside ? 'inside' : 'checked_out',
        'checked_in_at' => now()->subHours(rand(1, 8)),
        'checked_out_at' => $inside ? null : now()->subHours(rand(1, 3)),
        'badge_no' => 'V' . str_pad((string)($i + 1), 4, '0', STR_PAD_LEFT),
    ]);
}
echo "  ✅ 15 visitors created\n";

// ─── 8. WALLET TOPUPS (for some students) ───
echo "Creating wallet topups...\n";
$topupCount = 0;
foreach (array_slice($studentIds, 0, 20) as $sid) {
    WalletTransaction::create([
        'student_id' => $sid,
        'type' => 'topup',
        'amount' => rand(500, 3000),
        'balance_after' => rand(500, 3000),
        'category' => 'mpesa_topup',
        'status' => 'completed',
        'phone' => Student::find($sid)->parent_phone,
        'mpesa_receipt' => 'M' . strtoupper(\Illuminate\Support\Str::random(9)),
        'reference' => 'MPESA-DEMO-' . $sid,
        'description' => 'Demo M-Pesa topup',
    ]);
    $topupCount++;
}
echo "  ✅ $topupCount wallet topups created\n";

DB::commit();

echo "\n═══════════════════════════════════════════════════\n";
echo "  ✅ DEMO DATA SEEDED SUCCESSFULLY\n";
echo "═══════════════════════════════════════════════════\n\n";

echo "LOGIN CREDENTIALS (password: password)\n";
echo "─────────────────────────────────────────────────\n";
echo "Principal:  admin@marell.ac.ke\n";
echo "DOS:        dos@marell.ac.ke\n";
echo "Bursar:     bursar@marell.ac.ke\n";
echo "Teacher:    teacher1@marell.ac.ke\n";
echo "Guard 1:    guard1@marell.ac.ke\n";
echo "Guard 2:    guard2@marell.ac.ke\n";
echo "\nParent sample phone: " . Student::first()->parent_phone . "\n";
echo "Parent OTP: 000000 (check your OTP setting)\n";

echo "\nKEY PAGES TO SHOW\n";
echo "─────────────────────────────────────────────────\n";
echo "Live Collection:      /principal/live-collection\n";
echo "Canteen Report:       /principal/canteen-report\n";
echo "Canteen Items:        /principal/canteen-items\n";
echo "Staff Attendance:     /principal/staff-attendance\n";
echo "Staff QR Codes:       /principal/staff-qrcodes\n";
echo "Guard Console:        /gate/console\n";
echo "Canteen Menu:         /canteen/menu\n";
echo "Parent Portal:        /parent\n";

echo "\n";

} catch (\Throwable $e) {
    DB::rollBack();
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Location: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\n💡 All data rolled back — nothing was saved.\n";
    exit(1);
}
