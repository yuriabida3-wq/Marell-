<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('timetables', function (Blueprint $table) {
            $table->string('term', 20)->default('Term 1')->after('stream');
            $table->string('year', 10)->default('2026')->after('term');

            // Drop old unique, add new one that includes term/year
            $table->dropUnique('uniq_class_slot');
            $table->unique(['class','stream','term','year','day','period'], 'uniq_class_term_slot');
        });
    }
    public function down(): void {
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropUnique('uniq_class_term_slot');
            $table->unique(['class','stream','day','period'], 'uniq_class_slot');
            $table->dropColumn(['term','year']);
        });
    }
};
