<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // CP-22a: canteen_items is already created by the earlier
        // 2026_09_22_000020_create_wallet_tables migration. On a fresh
        // install this migration previously died with a duplicate-table
        // error and blocked every subsequent migration. Guard it.
        if (Schema::hasTable('canteen_items')) {
            return;
        }

        Schema::create('canteen_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('general');
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0);
            $table->boolean('active')->default(true);
            $table->boolean('blockable')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // No-op by design: 2026_09_22_000020 owns the drop. Rolling back
        // this migration must not destroy a table it did not create.
    }
};