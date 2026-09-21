<?php

namespace App\Services;

use App\Models\Student;

class SiblingDiscountService
{
    /**
     * Discount tiers per sibling order:
     * 1st child: 0%
     * 2nd child: 5%
     * 3rd+ child: 10%
     */
    public const TIERS = [
        1 => 0.00,
        2 => 0.05,
        3 => 0.10,
    ];

    public static function discountFor(int $order): float
    {
        if ($order <= 1) return 0.00;
        if ($order === 2) return 0.05;
        return 0.10;
    }

    /**
     * Recompute sibling order + discount for all students
     * sharing the same parent_group_key.
     */
    public static function recomputeFor(string $parentGroupKey): int
    {
        $students = Student::where('parent_group_key', $parentGroupKey)
            ->orderBy('id')
            ->get();

        $count = $students->count();
        $i = 1;

        foreach ($students as $s) {
            $rate = self::discountFor($i);
            $baseFee = (float) $s->total_fee;
            $discount = round($baseFee * $rate, 2);
            $newTotal = max(0, $baseFee - $discount);
            $newBalance = max(0, $newTotal - (float) $s->paid_amount);

            $s->update([
                'sibling_order'   => $i,
                'discount_amount' => $discount,
                'balance'         => $newBalance,
            ]);
            $i++;
        }

        return $count;
    }

    /**
     * Recompute ALL groups (used as a maintenance command).
     */
    public static function recomputeAll(): int
    {
        $keys = Student::whereNotNull('parent_group_key')
            ->distinct()
            ->pluck('parent_group_key');

        $total = 0;
        foreach ($keys as $key) {
            $total += self::recomputeFor($key);
        }
        return $total;
    }
}
