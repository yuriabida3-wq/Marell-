<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherCheckin extends Model
{
    protected $fillable = ['user_id','date','clock_in','clock_out','ip','note'];
    protected $casts = ['date' => 'date'];

    public function teacher() { return $this->belongsTo(User::class, 'user_id'); }

    public function hoursWorked(): float
    {
        if (!$this->clock_in || !$this->clock_out) return 0;
        $in = strtotime($this->clock_in);
        $out = strtotime($this->clock_out);
        return round(($out - $in) / 3600, 2);
    }
}
