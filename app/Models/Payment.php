<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id', 'amount', 'initiated_amount', 'method',
        'transaction_code', 'checkout_request_id',
        'status', 'receipt_no', 'exam_id',
        'term', 'year', 'recorded_by', 'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'initiated_amount' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Generate next receipt number.
     * Uses a dedicated counter row per year, locked with lockForUpdate()
     * inside its own transaction. Serializes all concurrent callers
     * regardless of database isolation level. Format RCP-YYYY-NNNNN.
     */
    public static function generateReceiptNo(): string
    {
        $year = (int) date('Y');

        return DB::transaction(function () use ($year) {
            DB::table('receipt_counters')->insertOrIgnore([
                'year'       => $year,
                'last'       => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $current = (int) DB::table('receipt_counters')
                ->where('year', $year)
                ->lockForUpdate()
                ->value('last');

            $next = $current + 1;

            DB::table('receipt_counters')
                ->where('year', $year)
                ->update(['last' => $next, 'updated_at' => now()]);

            return sprintf('RCP-%d-%05d', $year, $next);
        });
    }
}
