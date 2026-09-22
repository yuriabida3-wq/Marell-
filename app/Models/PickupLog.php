<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupLog extends Model
{
    protected $fillable = ['approved_pickup_id','student_id','picker_name','picker_phone','relationship','result','reason','guard_id','guard_name','ip'];
    protected $casts = ['created_at' => 'datetime'];

    public function student() { return $this->belongsTo(Student::class); }
    public function pickup()  { return $this->belongsTo(ApprovedPickup::class, 'approved_pickup_id'); }
    public function guardUser() { return $this->belongsTo(User::class, 'guard_id'); }

    public function resultColor(): string
    {
        return match ($this->result) {
            'verified' => 'bg-green-100 text-green-700',
            'denied'   => 'bg-red-100 text-red-700',
            'manual'   => 'bg-yellow-100 text-yellow-700',
            default    => 'bg-gray-100 text-gray-600',
        };
    }
}
