<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Timetable extends Model {
    use HasFactory;
    protected $fillable = ['class','stream','day','period','subject','teacher_id','start_time','end_time'];
    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }
}
