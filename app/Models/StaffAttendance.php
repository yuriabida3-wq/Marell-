<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class StaffAttendance extends Model {
    protected $fillable = ["user_id", "action", "scanned_at"];
    public function user() { return $this->belongsTo(User::class); }
}
