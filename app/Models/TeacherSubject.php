<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherSubject extends Model
{
    protected $fillable = ['teacher_id','subject','class','stream','periods_per_week','active'];
    protected $casts = ['active' => 'boolean', 'periods_per_week' => 'integer'];

    public function teacher() { return $this->belongsTo(User::class, 'teacher_id'); }

    public function scopeActive($q) { return $q->where('active', true); }
}
