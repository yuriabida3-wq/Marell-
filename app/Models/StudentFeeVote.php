<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class StudentFeeVote extends Model {
    protected $fillable = ['student_id','vote_head_id','amount_due','amount_paid'];
    protected $casts = [
        'amount_due' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function student() { return $this->belongsTo(Student::class); }
    public function voteHead() { return $this->belongsTo(VoteHead::class); }

    public function balance() {
        return max(0, (float) $this->amount_due - (float) $this->amount_paid);
    }
}
