<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PanicAlert extends Model
{
    protected $fillable = ['user_id','user_name','location','note','ip','sms_sent','sms_failed','resolved_at','resolved_by'];
    protected $casts = ['resolved_at' => 'datetime'];

    public function user()     { return $this->belongsTo(User::class); }
    public function resolver() { return $this->belongsTo(User::class, 'resolved_by'); }

    public function isResolved(): bool
    {
        return $this->resolved_at !== null;
    }
}
