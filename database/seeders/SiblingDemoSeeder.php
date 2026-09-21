<?php

namespace Database\Seeders;

use App\Models\Student;
use Illuminate\Database\Seeder;

class SiblingDemoSeeder extends Seeder
{
    public function run(): void
    {
        $families = [
            ['Wanjiku Family', '254720001111', ['Brian Wanjiku', 'Mary Wanjiku', 'Kevin Wanjiku']],
            ['Otieno Family',  '254720002222', ['Faith Otieno', 'Peter Otieno', 'Grace Otieno', 'John Otieno']],
            ['Kiprop Family',  '254720003333', ['Samuel Kiprop', 'Lucy Kiprop']],
        ];

        $startAdm = 9000;
        foreach ($families as $family) {
            [$fname, $phone, $names] = $family;
            $i = 0;
            foreach ($names as $name) {
                $adm = sprintf('MAR-%d-%04d', date('Y'), $startAdm++);
                $fee = 50000;
                Student::updateOrCreate(
                    ['adm_no' => $adm],
                    [
                        'name'         => $name,
                        'class'        => ['Class 5', 'Class 6', 'Class 7', 'Class 8'][$i % 4],
                        'stream'       => 'Blue',
                        'parent_name'  => $fname,
                        'parent_phone' => $phone,
                        'parent_email' => null,
                        'total_fee'    => $fee,
                        'paid_amount'  => 0,
                        'balance'      => $fee,
                        'status'       => 'active',
                    ]
                );
                $i++;
            }
        }

        $this->command->info('Sibling families created: 3 (9 students)');
    }
}
