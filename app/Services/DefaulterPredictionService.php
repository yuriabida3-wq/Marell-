<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Student;
use Carbon\Carbon;

class DefaulterPredictionService
{
    /**
     * Score each active student on 0-100 likelihood of defaulting next month.
     * Uses simple heuristic rules (no external AI API needed):
     *   - Days since last payment
     *   - Trend of last 3 payments (declining = risky)
     *   - Balance / Total Fee ratio
     *   - Sibling risk propagation (if 1 child in family defaults, others at risk)
     */
    public static function predictAll(): array
    {
        $students = Student::where('status', 'active')->with('payments')->get();
        $predictions = [];
        $familyRisk = [];

        // First pass: individual scores
        foreach ($students as $student) {
            $score = self::scoreStudent($student);
            $predictions[$student->id] = [
                'student' => $student,
                'score' => $score,
                'reasons' => self::reasons($student),
            ];
        }

        // Second pass: family risk aggregation
        foreach ($predictions as $id => $p) {
            $key = $p['student']->parent_group_key;
            if (!$key) continue;
            $familyRisk[$key] = $familyRisk[$key] ?? [];
            $familyRisk[$key][] = $p['score'];
        }

        // Apply family penalty: if average family score > 60, add 10 to each member
        foreach ($predictions as $id => $p) {
            $key = $p['student']->parent_group_key;
            if ($key && isset($familyRisk[$key]) && count($familyRisk[$key]) > 1) {
                $familyAvg = array_sum($familyRisk[$key]) / count($familyRisk[$key]);
                if ($familyAvg > 60) {
                    $predictions[$id]['score'] = min(100, $predictions[$id]['score'] + 10);
                    $predictions[$id]['reasons'][] = 'Family default pattern detected';
                }
            }
        }

        // Sort by score descending
        uasort($predictions, fn($a, $b) => $b['score'] <=> $a['score']);

        return array_values($predictions);
    }

    public static function scoreStudent(Student $student): int
    {
        $score = 0;
        $balance = (float) $student->balance;
        $totalFee = (float) $student->total_fee;

        if ($totalFee <= 0 || $balance <= 0) return 0;

        // Factor 1: Balance ratio (max 40 pts)
        $ratio = $balance / $totalFee;
        $score += (int) round($ratio * 40);

        // Factor 2: Days since last payment (max 30 pts)
        $lastPayment = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->latest('created_at')
            ->first();

        if (!$lastPayment) {
            $score += 30; // Never paid
        } else {
            $daysSince = Carbon::parse($lastPayment->created_at)->diffInDays(now());
            if ($daysSince > 90)      $score += 30;
            elseif ($daysSince > 60)  $score += 22;
            elseif ($daysSince > 30)  $score += 12;
            elseif ($daysSince > 14)  $score += 5;
        }

        // Factor 3: Payment trend (max 20 pts)
        $recent = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->latest('created_at')
            ->take(3)
            ->pluck('amount')
            ->map(fn($v) => (float) $v)
            ->toArray();

        if (count($recent) >= 2) {
            if ($recent[0] < $recent[1] * 0.5) {
                $score += 15; // Sharp decline
            } elseif ($recent[0] < $recent[1]) {
                $score += 8;
            }
        }

        // Factor 4: Has late fines (max 10 pts)
        if ((float) $student->fines_total > 0) {
            $score += 10;
        }

        return min(100, $score);
    }

    public static function reasons(Student $student): array
    {
        $reasons = [];
        $balance = (float) $student->balance;
        $totalFee = (float) $student->total_fee;

        if ($totalFee > 0) {
            $ratio = $balance / $totalFee;
            if ($ratio > 0.7) $reasons[] = 'Balance > 70% of fee';
            elseif ($ratio > 0.5) $reasons[] = 'Balance > 50% of fee';
        }

        $lastPayment = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->latest('created_at')
            ->first();

        if (!$lastPayment) {
            $reasons[] = 'Never made a payment';
        } else {
            $daysSince = Carbon::parse($lastPayment->created_at)->diffInDays(now());
            if ($daysSince > 60) $reasons[] = "{$daysSince} days since last payment";
        }

        $recent = Payment::where('student_id', $student->id)
            ->where('status', 'completed')
            ->latest('created_at')
            ->take(3)
            ->pluck('amount')
            ->map(fn($v) => (float) $v)
            ->toArray();

        if (count($recent) >= 2 && $recent[0] < $recent[1] * 0.5) {
            $reasons[] = 'Payment amount declining sharply';
        }

        if ((float) $student->fines_total > 0) {
            $reasons[] = 'Has late payment fines';
        }

        return $reasons;
    }

    public static function riskLevel(int $score): string
    {
        if ($score >= 70) return 'red';
        if ($score >= 40) return 'yellow';
        return 'green';
    }
}
