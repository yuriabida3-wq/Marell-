<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','completed','failed','under_reconciliation','expired') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Refuse to roll back if any row uses the new value.
        $inUse = DB::table('payments')->where('status', 'expired')->count();
        if ($inUse > 0) {
            throw new \RuntimeException("Cannot roll back: {$inUse} payments use 'expired'.");
        }
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','completed','failed','under_reconciliation') NOT NULL DEFAULT 'pending'");
    }
};
