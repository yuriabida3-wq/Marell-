<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Admission extends Model {
    use HasFactory;
    protected $fillable = ['student_name','dob','class_applying','parent_name','parent_phone','parent_email','message','consent','status'];
}
