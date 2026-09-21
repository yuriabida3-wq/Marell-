<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class LateFine extends Model {
    protected $fillable = ['student_id','amount','reason','applied_date','due_before','waived','waived_reason','waived_by'];
    protected $casts = [
        'amount' => 'decimal:2',
        'applied_date' => 'date',
        'due_before' => 'date',
        'waived' => 'boolean',
    ];

    public function student() { return $this->belongsTo(Student::class); }
    public function waver()   { return $this->belongsTo(User::class, 'waived_by'); }
}
