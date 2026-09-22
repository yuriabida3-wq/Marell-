<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->string('status')->default('completed')->after('category');
            $table->string('checkout_request_id')->nullable()->after('status');
            $table->string('mpesa_receipt')->nullable()->after('checkout_request_id');
            $table->string('phone')->nullable()->after('mpesa_receipt');
        });
    }
    public function down(): void {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropColumn(['status', 'checkout_request_id', 'mpesa_receipt', 'phone']);
        });
    }
};
