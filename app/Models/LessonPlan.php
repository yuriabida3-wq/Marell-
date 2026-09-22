<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonPlan extends Model
{
    protected $fillable = ['teacher_id','class','stream','subject','week_starting','topic','objectives','activities','status','review_notes','reviewed_by','submitted_at','reviewed_at'];
    protected $casts = [
        'week_starting' => 'date',
        'submitted_at'  => 'datetime',
        'reviewed_at'   => 'datetime',
    ];

    public function teacher()  { return $this->belongsTo(User::class, 'teacher_id'); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function statusColor(): string
    {
        return match ($this->status) {
            'approved'  => 'bg-green-100 text-green-700',
            'rejected'  => 'bg-red-100 text-red-700',
            'submitted' => 'bg-blue-100 text-blue-700',
            default     => 'bg-gray-100 text-gray-700',
        };
    }
}
