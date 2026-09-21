<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class VoteHead extends Model {
    protected $fillable = ['code','name','description','active'];
    protected $casts = ['active' => 'boolean'];

    public function studentVotes() { return $this->hasMany(StudentFeeVote::class); }
}
