<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExamControl extends Model
{
    protected $fillable = ['exam_id','class','stream','action','notes','actor_id','actor_ip'];

    public function exam()  { return $this->belongsTo(Exam::class); }
    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }
}
