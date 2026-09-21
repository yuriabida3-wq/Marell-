<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = ['name','phone','id_number','purpose','host_name','host_type','student_adm','vehicle_plate','photo','status','checked_in_at','checked_out_at','badge_no','notes'];
    protected $casts = ['checked_in_at' => 'datetime', 'checked_out_at' => 'datetime'];

    public function scopeCurrentlyInside($q) { return $q->where('status', 'checked_in'); }
}
