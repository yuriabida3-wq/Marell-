<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherPerformance extends Model
{
    protected $table = 'teacher_performance';

    protected $fillable = ['user_id','month','score','attendance_score','marks_entry_score','lesson_plan_score','class_performance_score','parent_complaints','notes'];
    protected $casts = ['month' => 'date'];

    public function teacher() { return $this->belongsTo(User::class, 'user_id'); }

    public function grade(): string
    {
        return match (true) {
            $this->score >= 90 => 'A',
            $this->score >= 80 => 'B+',
            $this->score >= 70 => 'B',
            $this->score >= 60 => 'C+',
            $this->score >= 50 => 'C',
            default => 'D',
        };
    }

    public function gradeColor(): string
    {
        return match (true) {
            $this->score >= 80 => 'text-green-600',
            $this->score >= 60 => 'text-yellow-600',
            default => 'text-red-600',
        };
    }
}
