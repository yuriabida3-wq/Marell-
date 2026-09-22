<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = ['student_id','date','status','reason','marked_by','marked_by_name','arrival_time','sms_sent','sms_sent_at'];
    protected $casts = [
        'date'        => 'date',
        'sms_sent'    => 'boolean',
        'sms_sent_at' => 'datetime',
    ];

    public function student() { return $this->belongsTo(Student::class); }
    public function marker()  { return $this->belongsTo(User::class, 'marked_by'); }

    public function statusColor(): string
    {
        return match ($this->status) {
            'present' => 'bg-green-100 text-green-700',
            'absent'  => 'bg-red-100 text-red-700',
            'late'    => 'bg-yellow-100 text-yellow-700',
            'excused' => 'bg-blue-100 text-blue-700',
            'sick'    => 'bg-purple-100 text-purple-700',
            default   => 'bg-gray-100 text-gray-700',
        };
    }

    public function statusIcon(): string
    {
        return match ($this->status) {
            'present' => '✅',
            'absent'  => '❌',
            'late'    => '⏰',
            'excused' => '📝',
            'sick'    => '🤒',
            default   => '—',
        };
    }
}
