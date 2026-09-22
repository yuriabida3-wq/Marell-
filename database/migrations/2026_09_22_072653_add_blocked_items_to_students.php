<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->json('blocked_items')->nullable()->after('low_balance_alert');
            $table->json('blocked_categories')->nullable()->after('blocked_items');
        });
    }
    public function down(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['blocked_items', 'blocked_categories']);
        });
    }
};
