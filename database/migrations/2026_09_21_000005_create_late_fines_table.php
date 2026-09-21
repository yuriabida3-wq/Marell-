<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('late_fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('reason', 120);
            $table->date('applied_date')->index();
            $table->date('due_before')->nullable();
            $table->boolean('waived')->default(false);
            $table->string('waived_reason', 200)->nullable();
            $table->foreignId('waived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['student_id', 'applied_date'], 'uniq_student_fine_day');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->decimal('fines_total', 12, 2)->default(0)->after('discount_amount');
        });
    }
    public function down(): void {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('fines_total');
        });
        Schema::dropIfExists('late_fines');
    }
};
