<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('initiated_amount', 12, 2)->nullable()->after('amount');
        });
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->decimal('initiated_amount', 10, 2)->nullable()->after('amount');
        });

        // Backfill: best available evidence for existing rows.
        // Rows whose amount was previously overwritten by a callback
        // cannot recover the original value.
        DB::table('payments')->whereNull('initiated_amount')->update([
            'initiated_amount' => DB::raw('amount'),
        ]);
        DB::table('wallet_transactions')->whereNull('initiated_amount')->update([
            'initiated_amount' => DB::raw('amount'),
        ]);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('initiated_amount');
        });
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn('initiated_amount');
        });
    }
};