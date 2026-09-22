<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->decimal('daily_limit', 10, 2)->default(0)->after('wallet_balance');
            $table->decimal('low_balance_alert', 10, 2)->default(200)->after('daily_limit');
        });
    }
    public function down(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['daily_limit', 'low_balance_alert']);
        });
    }
};
