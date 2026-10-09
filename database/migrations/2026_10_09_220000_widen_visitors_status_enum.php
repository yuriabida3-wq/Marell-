<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // CP-22c: closes the last column-drift item found by CP-22b.
        // Live visitors.status ENUM includes 'inside' and defaults to
        // it; no migration produced that shape. On a fresh install the
        // gate console's visitor check-in would fail with "Data
        // truncated for column 'status'".
        if (!Schema::hasTable('visitors')) {
            return;
        }

        DB::statement("
            ALTER TABLE visitors
            MODIFY COLUMN status ENUM('expected','inside','checked_in','checked_out')
            NOT NULL DEFAULT 'inside'
        ");
    }

    public function down(): void
    {
        // No-op by design: narrowing back would drop any rows currently
        // set to 'inside', and the widened shape is what the app uses.
    }
};