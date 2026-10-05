<?php

namespace Database\Seeders;

use App\Models\{Classroom, Contact, Exam, Expense, News, Payment, Result, Student, TeacherSubject, Timetable, User};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class FullDemoSeeder extends Seeder
{
    public function run(): void
    {
        $appName = config('app.name', 'School');
        $isCTK = str_contains($appName, 'Christ');
        $domain = $isCTK ? 'ctk.ac.ke' : 'marell.ac.ke';
        $admPrefix = $isCTK ? 'CTK' : 'MAR';
        $year = date('Y');

        $this->command->info("▶ Seeding {$appName} · domain={$domain} · ADM prefix={$admPrefix}");

        // ============ 1. TEACHERS ============
        $teacherData = [
            ['Jane Wanjiru',   'jane',   'Mathematics',    '254700100001'],
            ['Peter Omondi',   'peter',  'Sciences',       '254700100002'],
            ['Grace Mueni',    'grace',  'English',        '254700100003'],
            ['Samuel Kariuki', 'samuel', 'Kiswahili',      '254700100004'],
            ['Mary Achieng',   'mary',   'Social Studies', '254700100005'],
            ['David Mutua',    'david',  'ICT',            '254700100006'],
            ['Alice Njeri',    'alice',  'CRE',            '254700100007'],
        ];

        $teachers = collect();
        foreach ($teacherData as $t) {
            $u = User::firstOrCreate(
                ['email' => $t[1] . '@' . $domain],
                [
                    'name' => $t[0],
                    'phone' => $t[3],
                    'password' => Hash::make('123456'),
                    'role_label' => 'teacher',
                    'active' => true,
                ]
            );
            if (!$u->hasRole('teacher')) $u->assignRole('teacher');
            $teachers->push($u);
        }
        $this->command->info("  ✓ {$teachers->count()} teachers");

        // ============ 2. CLASSES ============
        $classData = [
            ['Class 5', 'Blue',  'Upper', 0],
            ['Class 6', 'Blue',  'Upper', 1],
            ['Class 6', 'Green', 'Upper', 2],
            ['Class 7', 'Blue',  'JSS',   3],
            ['Class 8', 'Blue',  'JSS',   4],
            ['Class 8', 'Green', 'JSS',   5],
        ];

        $classes = collect();
        foreach ($classData as $i => $c) {
            $classes->push(Classroom::firstOrCreate(
                ['name' => $c[0], 'stream' => $c[1]],
                [
                    'level' => $c[2],
                    'class_teacher_id' => $teachers[$c[3]]->id,
                    'capacity' => 45,
                    'active' => true,
                ]
            ));
        }
        $this->command->info("  ✓ {$classes->count()} classes");

        // ============ 3. STUDENTS - top up to 80 ============
        $firstNames = ['Brian','Mary','Kevin','Faith','Peter','Grace','John','Esther','Samuel','Lucy','David','Mercy','Daniel','Sarah','Joseph','Jane','Michael','Ruth','Isaac','Purity','James','Hannah','Collins','Naomi','Vincent','Winnie','Elijah','Cynthia','Chris','Lydia','Nick','Sharon','Alex','Brenda','Tony','Diana','Dennis','Pamela','Felix','Nancy','Isaac','Rose','Victor','Gloria','Mark','Catherine','Simon','Rachel','Bernard','Christine','Philip','Vivian','Antony','Joyce','Steven','Ivy','Charles','Winnie','George','Martha'];
        $lastNames = ['Otieno','Wanjiku','Mwangi','Achieng','Kamau','Njeri','Ochieng','Muthoni','Kiprop','Atieno','Njoroge','Chebet','Maina','Wangari','Odhiambo','Wairimu','Barasa','Nyambura','Karanja','Nasimiyu','Wafula','Muthama','Odongo','Mwikali','Kibet','Wekesa','Onyango','Adhiambo','Karanja','Nyokabi','Mutiso','Auma','Barasa','Kerubo','Njau','Chepkoech','Kiptoo','Wambui','Otieno','Auma','Kinyua','Njeri','Masinde','Anyango','Weche','Makokha','Wanjala','Nafula','Simiyu','Khakame','Mugisha','Njoroge','Owino','Kilonzo','Odhiambo','Cheptoo','Musa','Nduta','Owino','Kimani'];

        $existing = Student::count();
        $target = 80;
        $toAdd = max(0, $target - $existing);
        $added = 0;

        if ($toAdd > 0) {
            // Find highest ADM number for this year/prefix
            $lastAdm = Student::where('adm_no', 'like', "{$admPrefix}-{$year}-%")
                ->orderByDesc('adm_no')->value('adm_no');
            $nextNum = $lastAdm ? ((int) substr($lastAdm, -4)) + 1 : 1;

            for ($i = 0; $i < $toAdd; $i++) {
                $name = $firstNames[($i * 7) % count($firstNames)] . ' ' . $lastNames[($i * 3) % count($lastNames)];
                $class = $classes[$i % $classes->count()];
                $fee = 45000 + (($i % 4) * 5000);
                $paid = [0, round($fee * 0.4), round($fee * 0.7), $fee, round($fee * 0.5)][$i % 5];

                Student::create([
                    'adm_no' => sprintf('%s-%d-%04d', $admPrefix, $year, $nextNum),
                    'name' => $name,
                    'class' => $class->name,
                    'stream' => $class->stream,
                    'parent_name' => 'Mr. ' . explode(' ', $name)[0] . ' Sr.',
                    'parent_phone' => '2547' . str_pad((string) (11000000 + $nextNum + $i), 8, '0', STR_PAD_LEFT),
                    'parent_email' => 'parent' . $nextNum . '@demo.' . $domain,
                    'total_fee' => $fee,
                    'paid_amount' => $paid,
                    'balance' => max(0, $fee - $paid),
                    'qr_token' => Str::random(48),
                    'wallet_balance' => rand(0, 500),
                    'status' => 'active',
                ]);
                $nextNum++;
                $added++;
            }
        }
        $this->command->info("  ✓ " . Student::count() . " students (+{$added} new)");

        // ============ 4. TEACHER SUBJECTS ============
        TeacherSubject::truncate();
        foreach ($classes as $class) {
            foreach ($teachers as $t) {
                $subject = explode(' ', $teacherData[$teachers->search($t)][2])[0];
                TeacherSubject::create([
                    'teacher_id' => $t->id,
                    'subject' => $teacherData[$teachers->search($t)][2],
                    'class' => $class->name,
                    'stream' => $class->stream,
                    'periods_per_week' => 5,
                    'active' => true,
                ]);
            }
        }
        $this->command->info("  ✓ " . TeacherSubject::count() . " teacher-subject records");

        // ============ 5. TIMETABLE ============
        $subjects = ['English','Kiswahili','Mathematics','Science','Social Studies','CRE','Agriculture','Creative Arts'];
        $days = ['Mon','Tue','Wed','Thu','Fri'];
        $times = [
            1 => ['08:00','08:40'], 2 => ['08:40','09:20'], 3 => ['09:20','10:00'],
            4 => ['10:20','11:00'], 5 => ['11:00','11:40'], 6 => ['11:40','12:20'],
            7 => ['14:00','14:40'], 8 => ['14:40','15:20'],
        ];

        Timetable::truncate();
        foreach ($classes as $class) {
            $idx = 0;
            foreach ($days as $day) {
                for ($p = 1; $p <= 8; $p++) {
                    Timetable::create([
                        'class' => $class->name,
                        'stream' => $class->stream,
                        'term' => 'Term 1',
                        'year' => (string) $year,
                        'day' => $day,
                        'period' => $p,
                        'subject' => $subjects[$idx % count($subjects)],
                        'teacher_id' => $teachers[$idx % $teachers->count()]->id,
                        'start_time' => $times[$p][0],
                        'end_time' => $times[$p][1],
                    ]);
                    $idx++;
                }
            }
        }
        $this->command->info("  ✓ " . Timetable::count() . " timetable slots");

        // ============ 6. PAYMENTS ============
        if (Payment::count() < 50) {
            $methods = ['M-Pesa','M-Pesa','M-Pesa','Cash','Bank'];
            $payCount = 0;
            foreach (Student::take(60)->get() as $student) {
                if ((float) $student->paid_amount <= 0) continue;
                $splits = rand(1, 2);
                $portion = $student->paid_amount / $splits;
                for ($i = 0; $i < $splits; $i++) {
                    $method = $methods[array_rand($methods)];
                    Payment::create([
                        'student_id' => $student->id,
                        'amount' => round($portion / 100) * 100,
                        'method' => $method,
                        'transaction_code' => $method === 'M-Pesa' ? 'Q' . strtoupper(Str::random(9)) : strtoupper($method . '-' . Str::random(6)),
                        'status' => 'completed',
                        'receipt_no' => sprintf('REC-%d-%05d', $year, 1000 + $payCount),
                        'term' => 'Term 1',
                        'year' => (string) $year,
                        'recorded_by' => 1,
                        'notes' => 'Demo seed',
                        'created_at' => now()->subDays(rand(1, 90)),
                    ]);
                    $payCount++;
                }
            }
            $this->command->info("  ✓ {$payCount} payments");
        }

        // ============ 7. EXAM + RESULTS ============
        $exam = Exam::firstOrCreate(
            ['name' => 'End of Term 1 Exam', 'term' => 'Term 1', 'year' => (string) $year],
            ['status' => 'published', 'created_by' => 1]
        );

        if (Result::where('exam_id', $exam->id)->count() < 100) {
            $resCount = 0;
            foreach (Student::all() as $student) {
                foreach ($subjects as $sub) {
                    $marks = rand(35, 95);
                    $grade = match(true) {
                        $marks>=80=>'A',$marks>=75=>'A-',$marks>=70=>'B+',$marks>=65=>'B',$marks>=60=>'B-',
                        $marks>=55=>'C+',$marks>=50=>'C',$marks>=45=>'C-',$marks>=40=>'D+',$marks>=35=>'D',default=>'E',
                    };
                    Result::updateOrCreate(
                        ['exam_id' => $exam->id, 'student_id' => $student->id, 'subject' => $sub],
                        ['marks' => $marks, 'grade' => $grade, 'teacher_id' => $teachers[array_rand($teachers->toArray())]->id ?? null]
                    );
                    $resCount++;
                }
            }
            $this->command->info("  ✓ {$resCount} results");
        }

        // ============ 8. NEWS ============
        if (News::count() < 4) {
            $newsData = [
                ['Sports Day 2026 A Roaring Success', 'Our annual sports gala brought out the best in every learner.', 'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=1200'],
                ['KCSE Excellence — 98% Pass Rate', 'Another stellar performance with 98% qualifying for university.', 'https://images.unsplash.com/photo-1523050854058-8df90110c9f1?w=1200'],
                ['New Science Lab Opens', 'State-of-the-art lab ready for hands-on CBC learning.', 'https://images.unsplash.com/photo-1532094349884-543bc11b234d?w=1200'],
                ['Grade 9 Transition Orientation', 'Parents invited to comprehensive CBC transition workshop.', 'https://images.unsplash.com/photo-1580582932707-520aed937b7b?w=1200'],
            ];
            foreach ($newsData as $n) {
                News::firstOrCreate(
                    ['title' => $n[0]],
                    [
                        'slug' => Str::slug($n[0]) . '-' . uniqid(),
                        'excerpt' => $n[1],
                        'body' => $n[1] . "\n\n" . str_repeat("{$appName} continues to lead in academic excellence, character formation, and innovation.\n\n", 3),
                        'image' => $n[2],
                        'author_id' => 1,
                        'published' => true,
                    ]
                );
            }
            $this->command->info("  ✓ " . News::count() . " news posts");
        }

        // ============ 9. EXPENSES ============
        if (Expense::count() < 8) {
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
                Expense::firstOrCreate(
                    ['description' => $e[1]],
                    [
                        'category' => $e[0],
                        'amount' => $e[2],
                        'method' => ['Cash','Bank','M-Pesa'][$i % 3],
                        'expense_date' => now()->subDays(30 - ($i * 4))->toDateString(),
                        'recorded_by' => 1,
                    ]
                );
            }
            $this->command->info("  ✓ " . Expense::count() . " expenses");
        }

        // ============ 10. ADMISSIONS ============
        if (\App\Models\Admission::count() < 5) {
            $admissionsData = [
                ['Amina Hassan',   'Baby Class', 'Mrs. Halima Hassan', '254700300001'],
                ['Kevin Mwangi',   'Grade 3',    'Mr. John Mwangi',    '254700300002'],
                ['Brenda Nasimiyu','Grade 5',    'Mrs. Rose Nasimiyu', '254700300003'],
                ['Ian Kiptoo',     'Grade 8',    'Mr. David Kiptoo',   '254700300004'],
                ['Zara Ahmed',     'PP2',        'Mrs. Fatma Ahmed',   '254700300005'],
            ];
            foreach ($admissionsData as $i => $a) {
                \App\Models\Admission::firstOrCreate(
                    ['student_name' => $a[0]],
                    [
                        'class_applying' => $a[1],
                        'parent_name' => $a[2],
                        'parent_phone' => $a[3],
                        'parent_email' => 'parent' . $i . '@demo.' . $domain,
                        'message' => 'Interested in joining ' . $appName,
                        'consent' => true,
                        'status' => ['new','contacted','admitted','new','new'][$i],
                    ]
                );
            }
            $this->command->info("  ✓ " . \App\Models\Admission::count() . " admissions");
        }

        // ============ 11. CONTACTS ============
        if (Contact::count() < 4) {
            $contactsData = [
                ['Mr. Joseph Kariuki', 'jkariuki@example.com', '254700200001', 'Fee structure enquiry', 'Please advise fee structure for Grade 4.'],
                ['Mrs. Faith Wangari', 'fwangari@example.com', '254700200002', 'School bus route', 'Do you offer bus to Milimani? Monthly cost?'],
                ['Mr. David Otieno',   'dotieno@example.com',  '254700200003', 'KCSE performance', 'Do you have Form 1 openings?'],
                ['Ms. Sarah Njoki',    'snjoki@example.com',   '254700200004', 'Sports program', 'What extra-curricular activities do you offer?'],
            ];
            foreach ($contactsData as $c) {
                Contact::firstOrCreate(
                    ['name' => $c[0], 'subject' => $c[3]],
                    ['email' => $c[1], 'phone' => $c[2], 'message' => $c[4], 'handled' => false]
                );
            }
            $this->command->info("  ✓ " . Contact::count() . " contacts");
        }

        $this->command->info("🎉 {$appName} demo data complete");
    }
}
