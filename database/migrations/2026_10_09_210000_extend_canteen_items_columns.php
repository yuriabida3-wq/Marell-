<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('canteen_items')) {
            return;
        }

        // Widen columns to the shape the later migration intended and
        // the live application already uses. Guarded per-column so this
        // is a no-op on the live database.
        DB::statement("ALTER TABLE canteen_items MODIFY COLUMN name VARCHAR(255) NOT NULL");
        DB::statement("ALTER TABLE canteen_items MODIFY COLUMN category VARCHAR(255) NOT NULL DEFAULT 'general'");
        DB::statement("ALTER TABLE canteen_items MODIFY COLUMN price DECIMAL(10,2) NOT NULL");

        if (!Schema::hasColumn('canteen_items', 'stock')) {
            DB::statement("ALTER TABLE canteen_items ADD COLUMN stock INT NOT NULL DEFAULT 0");
        }
        if (!Schema::hasColumn('canteen_items', 'blockable')) {
            DB::statement("ALTER TABLE canteen_items ADD COLUMN blockable TINYINT(1) NOT NULL DEFAULT 1");
        }
        if (!Schema::hasColumn('canteen_items', 'image')) {
            DB::statement("ALTER TABLE canteen_items ADD COLUMN image VARCHAR(255) NULL");
        }
    }

    public function down(): void
    {
        // No-op by design: narrowing columns back could lose data and
        // the widened shape is what the application requires.
    }
};