<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\Exam;
use App\Models\Expense;
use App\Models\News;
use App\Models\Payment;
use App\Models\Result;
use App\Models\Student;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // ============ 1. TEACHERS ============
        $teacherData = [
            ['Jane Wanjiru',    'jane@marell.ac.ke',    '254700100001', 'Mathematics'],
            ['Peter Omondi',    'peter@marell.ac.ke',   '254700100002', 'Sciences'],
            ['Grace Mueni',     'grace@marell.ac.ke',   '254700100003', 'English'],
            ['Samuel Kariuki',  'samuel@marell.ac.ke',  '254700100004', 'Kiswahili'],
            ['Mary Achieng',    'mary@marell.ac.ke',    '254700100005', 'Social Studies'],
            ['David Mutua',     'david@marell.ac.ke',   '254700100006', 'ICT'],
        ];

        $teachers = [];
        foreach ($teacherData as $t) {
            $u = User::firstOrCreate(
                ['email' => $t[1]],
                [
                    'name' => $t[0],
                    'phone' => $t[2],
                    'password' => Hash::make('123456'),
                    'role_label' => 'teacher',
                    'active' => true,
                ]
            );
            if (!$u->hasRole('teacher')) $u->assignRole('teacher');
            $teachers[] = $u;
        }
        $this->command->info('✓ ' . count($teachers) . ' teachers ready');

        // ============ 2. CLASSES ============
        $classData = [
            ['Class 5', 'Blue',  'Upper', 0],
            ['Class 6', 'Blue',  'Upper', 1],
            ['Class 6', 'Green', 'Upper', 2],
            ['Class 7', 'Blue',  'JSS',   3],
            ['Class 8', 'Blue',  'JSS',   4],
            ['Class 8', 'Green', 'JSS',   5],
        ];

        $classes = [];
        foreach ($classData as $c) {
            $classes[] = Classroom::firstOrCreate(
                ['name' => $c[0], 'stream' => $c[1]],
                [
                    'level' => $c[2],
                    'class_teacher_id' => $teachers[$c[3]]->id,
                    'capacity' => 45,
                    'active' => true,
                ]
            );
        }
        $this->command->info('✓ ' . count($classes) . ' classes ready');

        // ============ 3. STUDENTS — 10 per class ============
        $firstNames = ['Brian','Mary','Kevin','Faith','Peter','Grace','John','Esther','Samuel','Lucy','David','Mercy','Daniel','Sarah','Joseph','Jane','Michael','Ruth','Isaac','Purity','James','Hannah','Collins','Naomi','Vincent','Winnie','Elijah','Esther','Chris','Lydia','Nick','Sharon','Alex','Brenda','Tony','Cynthia','Dennis','Pamela','Felix','Nancy','Isaac','Rose','Victor','Diana','Mark','Gloria','Simon','Catherine','Bernard','Rachel','Philip','Christine','Antony','Winnie','Steven','Joyce','Charles','Vivian','George','Martha'];
        $lastNames  = ['Otieno','Wanjiku','Mwangi','Achieng','Kamau','Njeri','Ochieng','Muthoni','Kiprop','Atieno','Njoroge','Chebet','Maina','Wangari','Odhiambo','Wairimu','Barasa','Nyambura','Karanja','Nasimiyu','Wafula','Muthama','Odongo','Mwikali','Kibet','Wekesa','Onyango','Adhiambo','Karanja','Nyokabi','Mutiso','Auma','Barasa','Kerubo','Njau','Chepkoech','Kiptoo','Wambui','Otieno','Auma','Kinyua','Njeri','Masinde','Anyango','Weche','Makokha','Wanjala','Nafula','Simiyu','Khakame','Mugisha','Njoroge','Owino','Kilonzo','Odhiambo','Cheptoo','Musa','Nduta','Owino','Kimani'];

        $studentCount = 0;
        $year = date('Y');
        $admIndex = 1;

        foreach ($classes as $class) {
            for ($i = 0; $i < 10; $i++) {
                $name  = $firstNames[($studentCount * 7) % count($firstNames)] . ' ' . $lastNames[($studentCount * 3) % count($lastNames)];
                $fee   = 45000 + (($admIndex % 4) * 5000);
                $paid  = [0, $fee * 0.4, $fee * 0.7, $fee, $fee * 0.5][$admIndex % 5];
                $paid  = round($paid / 100) * 100;

                Student::updateOrCreate(
                    ['adm_no' => sprintf('MAR-%d-%04d', $year, $admIndex)],
                    [
                        'name' => $name,
                        'class' => $class->name,
                        'stream' => $class->stream,
                        'parent_name' => 'Mr. ' . explode(' ', $name)[0] . ' Sr.',
                        'parent_phone' => '2547' . str_pad((string) (11000000 + $admIndex), 8, '0', STR_PAD_LEFT),
                        'parent_email' => 'parent' . $admIndex . '@marell.parent',
                        'total_fee' => $fee,
                        'paid_amount' => $paid,
                        'balance' => max(0, $fee - $paid),
                        'status' => 'active',
                    ]
                );
                $studentCount++;
                $admIndex++;
            }
        }
        $this->command->info("✓ {$studentCount} students ready");

        // ============ 4. TIMETABLE for each class ============
        $subjects = ['English','Kiswahili','Mathematics','Science','Social Studies','CRE','Agriculture','Creative Arts'];
        $days = ['Mon','Tue','Wed','Thu','Fri'];
        $times = [
            1 => ['08:00','08:40'], 2 => ['08:40','09:20'], 3 => ['09:20','10:00'],
            4 => ['10:20','11:00'], 5 => ['11:00','11:40'], 6 => ['11:40','12:20'],
            7 => ['14:00','14:40'], 8 => ['14:40','15:20'],
        ];

        $ttCount = 0;
        foreach ($classes as $class) {
            Timetable::where('class', $class->name)->where('stream', $class->stream)->delete();
            $idx = 0;
            foreach ($days as $day) {
                for ($p = 1; $p <= 8; $p++) {
                    Timetable::create([
                        'class' => $class->name,
                        'stream' => $class->stream,
                        'day' => $day,
                        'period' => $p,
                        'subject' => $subjects[$idx % count($subjects)],
                        'teacher_id' => $teachers[$idx % count($teachers)]->id,
                        'start_time' => $times[$p][0],
                        'end_time' => $times[$p][1],
                    ]);
                    $idx++;
                    $ttCount++;
                }
            }
        }
        $this->command->info("✓ {$ttCount} timetable slots ready");

        // ============ 5. PAYMENTS — spread over last 90 days ============
        $methods = ['M-Pesa', 'M-Pesa', 'M-Pesa', 'Cash', 'Bank'];
        $payCount = 0;
        foreach (Student::all() as $student) {
            $needed = $student->paid_amount;
            if ($needed <= 0) continue;

            $splits = rand(1, 3);
            $portion = $needed / $splits;

            for ($i = 0; $i < $splits; $i++) {
                $method = $methods[array_rand($methods)];
                $daysAgo = rand(1, 90);

                $payment = Payment::create([
                    'student_id' => $student->id,
                    'amount' => round($portion / 100) * 100,
                    'method' => $method,
                    'transaction_code' => $method === 'M-Pesa'
                        ? 'Q' . strtoupper(Str::random(9))
                        : strtoupper($method . '-' . Str::random(8)),
                    'checkout_request_id' => null,
                    'status' => 'completed',
                    'receipt_no' => sprintf('REC-%d-%05d', $year, 1000 + $payCount),
                    'term' => 'Term 1',
                    'year' => (string) $year,
                    'recorded_by' => 1,
                    'notes' => 'Seeded payment',
                    'created_at' => now()->subDays($daysAgo),
                    'updated_at' => now()->subDays($daysAgo),
                ]);
                $payCount++;
            }
        }
        $this->command->info("✓ {$payCount} payments ready");

        // ============ 6. ONE EXAM + RESULTS ============
        $exam = Exam::firstOrCreate(
            ['name' => 'End of Term 1 Exam', 'term' => 'Term 1', 'year' => (string) $year],
            ['status' => 'published', 'created_by' => 1]
        );

        $resCount = 0;
        foreach (Student::all() as $student) {
            foreach ($subjects as $sub) {
                $marks = rand(35, 95);
                Result::updateOrCreate(
                    ['exam_id' => $exam->id, 'student_id' => $student->id, 'subject' => $sub],
                    [
                        'marks' => $marks,
                        'grade' => $this->grade($marks),
                        'teacher_id' => $teachers[array_rand($teachers)]->id,
                    ]
                );
                $resCount++;
            }
        }
        $this->command->info("✓ {$resCount} results ready");

        // ============ 7. NEWS ============
        $newsData = [
            ['Sports Day 2026 A Roaring Success', 'Our annual sports gala brought out the best in every learner.', 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=1200'],
            ['KCSE Excellence — 98% Pass Rate', 'Our candidates posted another stellar performance with 98% qualifying for university.', 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1200'],
            ['New Junior Secondary Science Lab Opens', 'State-of-the-art lab ready for hands-on CBC learning.', 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=1200'],
            ['Grade 9 Transition Orientation', 'Parents invited to a comprehensive workshop on the CBC transition.', 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1200'],
        ];

        foreach ($newsData as $n) {
            News::firstOrCreate(
                ['title' => $n[0]],
                [
                    'slug' => Str::slug($n[0]) . '-' . uniqid(),
                    'excerpt' => $n[1],
                    'body' => $n[1] . "\n\n" . str_repeat("Marell Academy continues to lead in academic excellence, character formation, and innovation. This event marked another milestone in our journey.\n\n", 3),
                    'image' => $n[2],
                    'author_id' => 1,
                    'published' => true,
                ]
            );
        }
        $this->command->info('✓ 4 news posts ready');

        // ============ 8. EXPENSES (past 30 days) ============
        $expensesData = [
            ['Salaries',    'Teaching staff monthly salaries',  420000],
            ['Utilities',   'KPLC electricity bill',            18500],
            ['Food',        'Lunch program supplies',           62000],
            ['Maintenance', 'Plumbing repairs block B',          9500],
            ['Transport',   'School bus fuel',                  24000],
            ['Stationery',  'Exam printing paper + toner',       11200],
            ['Utilities',   'Water bill',                         3800],
            ['Maintenance', 'Classroom painting',                15000],
        ];

        foreach ($expensesData as $i => $e) {
            Expense::create([
                'category' => $e[0],
                'description' => $e[1],
                'amount' => $e[2],
                'method' => ['Cash','Bank','M-Pesa'][$i % 3],
                'expense_date' => now()->subDays(30 - ($i * 4)),
                'recorded_by' => 1,
            ]);
        }
        $this->command->info('✓ 8 expenses ready');

        // ============ 9. CONTACTS ============
        $contactsData = [
            ['Mr. Joseph Kariuki', 'jkariuki@example.com', '254700200001', 'Inquiry about Grade 4 admission', 'Hello, I would like to know the fee structure for Grade 4 for my daughter. Please advise.'],
            ['Mrs. Faith Wangari', 'fwangari@example.com', '254700200002', 'School bus route', 'Do you offer school bus services to the Milimani area? What is the monthly cost?'],
            ['Mr. David Otieno',   'dotieno@example.com',  '254700200003', 'KCSE performance', 'Impressive KCSE results. Do you have openings in Form 1 for next year?'],
        ];
        foreach ($contactsData as $c) {
            \App\Models\Contact::firstOrCreate(
                ['name' => $c[0], 'subject' => $c[3]],
                ['email' => $c[1], 'phone' => $c[2], 'message' => $c[4], 'handled' => false]
            );
        }
        $this->command->info('✓ 3 contacts ready');

        // ============ 10. ADMISSIONS ============
        $admissionsData = [
            ['Amina Hassan',  'Baby Class',  'Mrs. Halima Hassan',  '254700300001', 'Wants to enroll daughter'],
            ['Kevin Mwangi',  'Grade 3',     'Mr. John Mwangi',     '254700300002', 'Transferring from Nairobi'],
            ['Brenda Nasimiyu','Grade 5',    'Mrs. Rose Nasimiyu',  '254700300003', 'Currently in public school'],
            ['Ian Kiptoo',    'Grade 8',     'Mr. David Kiptoo',    '254700300004', 'Looking for a better school'],
        ];
        foreach ($admissionsData as $i => $a) {
            \App\Models\Admission::firstOrCreate(
                ['student_name' => $a[0]],
                [
                    'dob' => now()->subYears(5 + $i)->toDateString(),
                    'class_applying' => $a[1],
                    'parent_name' => $a[2],
                    'parent_phone' => $a[3],
                    'parent_email' => 'parent' . $i . '@example.com',
                    'message' => $a[4],
                    'consent' => true,
                    'status' => ['new','contacted','admitted','new'][$i],
                ]
            );
        }
        $this->command->info('✓ 4 admissions ready');

        $this->command->info('');
        $this->command->info('🎉 DEMO DATA LOADED');
    }

    protected function grade(int $m): string
    {
        return match(true) {
            $m>=80=>'A',$m>=75=>'A-',$m>=70=>'B+',$m>=65=>'B',$m>=60=>'B-',
            $m>=55=>'C+',$m>=50=>'C',$m>=45=>'C-',$m>=40=>'D+',$m>=35=>'D',default=>'E',
        };
    }
}
