<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('date')->index();
            $table->enum('status', ['present', 'absent', 'late', 'excused', 'sick'])->default('present');
            $table->string('reason', 200)->nullable();
            $table->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('marked_by_name', 120)->nullable();
            $table->time('arrival_time')->nullable();
            $table->boolean('sms_sent')->default(false);
            $table->timestamp('sms_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'date'], 'uniq_student_date');
            $table->index(['date', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('attendances'); }
};
