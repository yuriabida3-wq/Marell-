<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->string('class', 30)->index();
            $table->string('stream', 30)->nullable();
            $table->enum('day', ['Mon', 'Tue', 'Wed', 'Thu', 'Fri']);
            $table->unsignedTinyInteger('period');
            $table->string('subject', 60);
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
            $table->unique(['class', 'stream', 'day', 'period'], 'uniq_class_slot');
        });
    }
    public function down(): void { Schema::dropIfExists('timetables'); }
};
