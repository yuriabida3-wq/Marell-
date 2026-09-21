<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'adm_no', 'name', 'class', 'stream',
        'parent_name', 'parent_phone', 'parent_email',
        'parent_group_key', 'sibling_order', 'discount_amount',
        'total_fee', 'paid_amount', 'balance',
        'photo', 'status',
    ];

    protected $casts = [
        'total_fee'       => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'balance'         => 'decimal:2',
        'discount_amount' => 'decimal:2',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_phone', 'id', 'phone');
    }

    public function feeVotes()
    {
        return $this->hasMany(StudentFeeVote::class);
    }
}
