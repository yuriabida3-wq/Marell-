<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'adm_no', 'name', 'class', 'stream',
        'parent_name', 'parent_phone', 'parent_email',
        'parent_group_key', 'sibling_order', 'discount_amount', 'fines_total',
        'qr_token', 'wallet_balance', 'auto_reload_threshold', 'auto_reload_amount',
        'auto_reload_phone', 'auto_reload_enabled',
        'total_fee', 'paid_amount', 'balance',
        'photo', 'status',
    ];

    protected $casts = [
        'total_fee'             => 'decimal:2',
        'paid_amount'           => 'decimal:2',
        'balance'               => 'decimal:2',
        'discount_amount'       => 'decimal:2',
        'fines_total'           => 'decimal:2',
        'wallet_balance'        => 'decimal:2',
        'auto_reload_threshold' => 'decimal:2',
        'auto_reload_amount'    => 'decimal:2',
        'auto_reload_enabled'   => 'boolean',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function results()
    {
        return $this->hasMany(Result::class);
    }

    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_phone', 'id', 'phone');
    }

    public function feeVotes()
    {
        return $this->hasMany(StudentFeeVote::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class)->latest();
    }

    public function approvedPickups()
    {
        return $this->hasMany(ApprovedPickup::class);
    }

    public function bookLoans()
    {
        return $this->hasMany(BookLoan::class);
    }

    // ============ WALLET HELPERS ============

    public function qrUrl(): string
    {
        return url('/student-qr/' . ($this->qr_token ?? ''));
    }

    public function walletBalance(): float
    {
        return (float) $this->wallet_balance;
    }

    public function topUpWallet(float $amount, ?string $reference = null, string $category = 'load', ?string $description = null, ?string $recordedBy = null, ?int $staffId = null): WalletTransaction
    {
        $newBalance = (float) $this->wallet_balance + $amount;
        $this->update(['wallet_balance' => $newBalance]);

        return WalletTransaction::create([
            'student_id'    => $this->id,
            'type'          => 'load',
            'amount'        => $amount,
            'balance_after' => $newBalance,
            'reference'     => $reference,
            'category'      => $category,
            'description'   => $description,
            'recorded_by'   => $recordedBy ?? (auth()->user()->name ?? 'System'),
            'staff_id'      => $staffId ?? auth()->id(),
            'ip'            => request()->ip(),
        ]);
    }

    public function spendWallet(float $amount, string $category = 'canteen', ?string $description = null, ?string $recordedBy = null, ?int $staffId = null): WalletTransaction
    {
        if ($amount > (float) $this->wallet_balance) {
            throw new \RuntimeException('Insufficient wallet balance.');
        }

        $newBalance = (float) $this->wallet_balance - $amount;
        $this->update(['wallet_balance' => $newBalance]);

        return WalletTransaction::create([
            'student_id'    => $this->id,
            'type'          => 'spend',
            'amount'        => $amount,
            'balance_after' => $newBalance,
            'category'      => $category,
            'description'   => $description,
            'recorded_by'   => $recordedBy ?? (auth()->user()->name ?? 'Canteen'),
            'staff_id'      => $staffId ?? auth()->id(),
            'ip'            => request()->ip(),
        ]);
    }
}
