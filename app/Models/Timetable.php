<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'class','stream','term','year','day','period',
        'subject','teacher_id','start_time','end_time',
    ];

    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }

    public function scopeForTerm($q, string $term, string $year) {
        return $q->where('term', $term)->where('year', $year);
    }
}
