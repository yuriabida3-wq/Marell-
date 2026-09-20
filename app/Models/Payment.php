<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'amount', 'method',
        'transaction_code', 'checkout_request_id',
        'status', 'receipt_no', 'exam_id',
        'term', 'year', 'recorded_by', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Generate next receipt number, collision-safe.
     * Uses MAX(existing sequence for year) + 1, not count().
     */
    public static function generateReceiptNo(): string
    {
        $year   = date('Y');
        $prefix = "REC-{$year}-";

        $max = self::where('receipt_no', 'like', "{$prefix}%")
            ->lockForUpdate()
            ->selectRaw('MAX(CAST(SUBSTRING(receipt_no, -5) AS UNSIGNED)) as max_seq')
            ->value('max_seq');

        $next = ((int) $max) + 1;

        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
