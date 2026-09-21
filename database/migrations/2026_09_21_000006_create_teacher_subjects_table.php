<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject', 60);
            $table->string('class', 30)->nullable();
            $table->string('stream', 30)->nullable();
            $table->unsignedTinyInteger('periods_per_week')->default(4);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['teacher_id', 'subject', 'class', 'stream'], 'uniq_teacher_assign');
        });
    }
    public function down(): void { Schema::dropIfExists('teacher_subjects'); }
};
