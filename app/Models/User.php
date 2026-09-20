<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password', 'phone', 'role_label', 'active'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'active'            => 'boolean',
        ];
    }

    public function students()
    {
        return $this->belongsToMany(
            Student::class, 'parent_student',
            'parent_phone', 'student_id',
            'phone', 'id'
        );
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class, 'teacher_id');
    }

    public function results()
    {
        return $this->hasMany(Result::class, 'teacher_id');
    }

    public function classrooms()
    {
        return $this->hasMany(Classroom::class, 'class_teacher_id');
    }
}
