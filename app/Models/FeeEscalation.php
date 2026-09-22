<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeEscalation extends Model
{
    protected $fillable = ['student_id','stage','channel','title','message','status','error','scheduled_for','sent_at'];
    protected $casts = [
        'scheduled_for' => 'date',
        'sent_at'       => 'datetime',
    ];

    public function student() { return $this->belongsTo(Student::class); }

    public function statusColor(): string
    {
        return match ($this->status) {
            'sent'    => 'bg-green-100 text-green-700',
            'failed'  => 'bg-red-100 text-red-700',
            'skipped' => 'bg-gray-100 text-gray-700',
            default   => 'bg-yellow-100 text-yellow-700',
        };
    }
}
