<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->string('parent_group_key', 40)->nullable()->index()->after('parent_phone');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('total_fee');
            $table->unsignedTinyInteger('sibling_order')->default(1)->after('discount_amount');
        });
    }
    public function down(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['parent_group_key', 'discount_amount', 'sibling_order']);
        });
    }
};
