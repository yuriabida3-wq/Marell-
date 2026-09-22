<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('teacher_checkins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('date')->index();
            $table->time('clock_in')->nullable();
            $table->time('clock_out')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('note', 200)->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'date'], 'uniq_teacher_checkin');
        });

        Schema::create('lesson_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('class', 30);
            $table->string('stream', 30)->nullable();
            $table->string('subject', 60);
            $table->date('week_starting');
            $table->string('topic', 200);
            $table->text('objectives')->nullable();
            $table->text('activities')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->text('review_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['teacher_id', 'week_starting']);
        });

        Schema::create('teacher_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('month');
            $table->unsignedSmallInteger('score')->default(0);
            $table->unsignedSmallInteger('attendance_score')->default(0);
            $table->unsignedSmallInteger('marks_entry_score')->default(0);
            $table->unsignedSmallInteger('lesson_plan_score')->default(0);
            $table->unsignedSmallInteger('class_performance_score')->default(0);
            $table->unsignedSmallInteger('parent_complaints')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'month']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('teacher_performance');
        Schema::dropIfExists('lesson_plans');
        Schema::dropIfExists('teacher_checkins');
    }
};
