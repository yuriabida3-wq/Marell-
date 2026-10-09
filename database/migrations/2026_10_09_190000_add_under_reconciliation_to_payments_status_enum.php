<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','completed','failed','under_reconciliation') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Refuse to roll back if any row uses the new value.
        $inUse = DB::table('payments')->where('status', 'under_reconciliation')->count();
        if ($inUse > 0) {
            throw new \RuntimeException("Cannot roll back: {$inUse} payments use 'under_reconciliation'.");
        }
        DB::statement("ALTER TABLE payments MODIFY status ENUM('pending','completed','failed') NOT NULL DEFAULT 'pending'");
    }
};