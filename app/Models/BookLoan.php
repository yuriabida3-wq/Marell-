<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BookLoan extends Model
{
    protected $fillable = ['book_id','student_id','issued_at','due_at','returned_at','fine','fine_paid','notes','issued_by'];
    protected $casts = [
        'issued_at'   => 'date',
        'due_at'      => 'date',
        'returned_at' => 'date',
        'fine'        => 'decimal:2',
        'fine_paid'   => 'boolean',
    ];

    public function book()    { return $this->belongsTo(Book::class); }
    public function student() { return $this->belongsTo(Student::class); }

    public function isOverdue(): bool
    {
        return !$this->returned_at && $this->due_at->isPast();
    }

    public function daysOverdue(): int
    {
        if ($this->returned_at) return 0;
        if (!$this->due_at->isPast()) return 0;
        return (int) $this->due_at->diffInDays(now());
    }

    public function calculateFine(): float
    {
        $settings = LibrarySetting::first();
        $rate = (float) ($settings->fine_per_day ?? 20);
        return $this->daysOverdue() * $rate;
    }
}
