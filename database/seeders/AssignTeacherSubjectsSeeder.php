<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignTeacherSubjectsSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = User::role('teacher')->get();
        $classes  = Classroom::where('active', true)->get();

        if ($teachers->isEmpty() || $classes->isEmpty()) {
            $this->command->error('No teachers or classes.');
            return;
        }

        // Subject pool per teacher — realistic assignment
        $subjectsByTeacher = [
            'English'    => ['English'],
            'Kiswahili'  => ['Kiswahili'],
            'Mathematics'=> ['Mathematics'],
            'Science'    => ['Science','Agriculture'],
            'Social'     => ['Social Studies','CRE'],
            'ICT'        => ['ICT','Creative Arts'],
        ];

        // Distribute subjects across teachers
        $teacherSubjects = [];
        $keys = array_keys($subjectsByTeacher);
        $i = 0;
        foreach ($teachers as $t) {
            $key = $keys[$i % count($keys)];
            $teacherSubjects[$t->id] = $subjectsByTeacher[$key];
            $i++;
        }

        TeacherSubject::truncate();

        foreach ($classes as $c) {
            foreach ($teachers as $t) {
                foreach ($teacherSubjects[$t->id] as $subj) {
                    TeacherSubject::create([
                        'teacher_id'       => $t->id,
                        'subject'          => $subj,
                        'class'            => $c->name,
                        'stream'           => $c->stream,
                        'periods_per_week' => 5,
                        'active'           => true,
                    ]);
                }
            }
        }

        $this->command->info('Assigned ' . TeacherSubject::count() . ' teacher-subject records.');
    }
}
