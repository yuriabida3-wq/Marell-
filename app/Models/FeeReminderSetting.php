<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeeReminderSetting extends Model
{
    protected $fillable = ['stage','name','days_offset','channel','template','active'];
    protected $casts = ['active' => 'boolean'];
}
