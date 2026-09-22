<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApprovedPickup extends Model
{
    protected $fillable = ['student_id','name','phone','relationship','id_number','photo','qr_token','active','created_by_parent'];
    protected $casts = ['active' => 'boolean'];

    public function student() { return $this->belongsTo(Student::class); }

    protected static function booted(): void
    {
        static::creating(function ($p) {
            if (empty($p->qr_token)) {
                $p->qr_token = Str::random(48);
            }
        });
    }

    public function qrUrl(): string
    {
        return url('/pickup/verify/' . $this->qr_token);
    }
}
