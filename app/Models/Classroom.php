<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'stream', 'level', 'class_teacher_id', 'capacity', 'active'];

    public function classTeacher()
    {
        return $this->belongsTo(User::class, 'class_teacher_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class', 'name')
                    ->where('stream', $this->stream);
    }

    public function studentCount()
    {
        $q = Student::where('class', $this->name);
        if ($this->stream) $q->where('stream', $this->stream);
        return $q->count();
    }

    public function label(): string
    {
        return $this->name . ($this->stream ? " {$this->stream}" : '');
    }
}
