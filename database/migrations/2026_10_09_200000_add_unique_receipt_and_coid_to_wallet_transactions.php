<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Safety gate: refuse if duplicates already exist. Better to fail
        // loudly than to silently attempt the ALTER and get a cryptic error.
        $dupReceipt = DB::select("
            SELECT mpesa_receipt, COUNT(*) AS c
            FROM wallet_transactions
            WHERE mpesa_receipt IS NOT NULL AND mpesa_receipt <> ''
            GROUP BY mpesa_receipt
            HAVING c > 1
        ");
        if (count($dupReceipt) > 0) {
            throw new RuntimeException(
                'Cannot add unique index: duplicate mpesa_receipt values exist ('
                . count($dupReceipt) . ' groups). Clean up first.'
            );
        }

        $dupCoid = DB::select("
            SELECT checkout_request_id, COUNT(*) AS c
            FROM wallet_transactions
            WHERE checkout_request_id IS NOT NULL AND checkout_request_id <> ''
            GROUP BY checkout_request_id
            HAVING c > 1
        ");
        if (count($dupCoid) > 0) {
            throw new RuntimeException(
                'Cannot add unique index: duplicate checkout_request_id values exist ('
                . count($dupCoid) . ' groups). Clean up first.'
            );
        }

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->unique('mpesa_receipt', 'wallet_transactions_mpesa_receipt_unique');
            $table->unique('checkout_request_id', 'wallet_transactions_checkout_request_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique('wallet_transactions_mpesa_receipt_unique');
            $table->dropUnique('wallet_transactions_checkout_request_id_unique');
        });
    }
};