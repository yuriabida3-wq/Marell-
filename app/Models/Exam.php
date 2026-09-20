<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model {
    use HasFactory;
    protected $fillable = ['name','term','year','status','created_by'];
    public function results() { return $this->hasMany(Result::class); }
}
