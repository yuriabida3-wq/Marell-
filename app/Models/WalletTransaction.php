<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = ['student_id','type','amount','balance_after','reference','category','description','recorded_by','staff_id','ip'];
    protected $casts = ['amount' => 'decimal:2', 'balance_after' => 'decimal:2'];

    public function student() { return $this->belongsTo(Student::class); }
    public function staff()   { return $this->belongsTo(User::class, 'staff_id'); }

    public function typeColor(): string
    {
        return match ($this->type) {
            'load'      => 'bg-green-100 text-green-700',
            'spend'     => 'bg-red-100 text-red-700',
            'refund'    => 'bg-blue-100 text-blue-700',
            'adjustment'=> 'bg-yellow-100 text-yellow-700',
            default     => 'bg-gray-100 text-gray-700',
        };
    }
}
