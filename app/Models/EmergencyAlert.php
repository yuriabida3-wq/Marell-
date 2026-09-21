<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmergencyAlert extends Model
{
    protected $fillable = ['title','message','severity','audience','status','total_recipients','sent_count','failed_count','sent_by','sent_at'];
    protected $casts = ['sent_at' => 'datetime'];

    public function sender() { return $this->belongsTo(User::class, 'sent_by'); }

    public function severityColor(): string
    {
        return match ($this->severity) {
            'critical' => 'bg-red-100 text-red-700 border-red-300',
            'warning'  => 'bg-yellow-100 text-yellow-700 border-yellow-300',
            default    => 'bg-blue-100 text-blue-700 border-blue-300',
        };
    }
}
