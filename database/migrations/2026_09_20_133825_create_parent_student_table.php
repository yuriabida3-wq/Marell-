<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('parent_student', function (Blueprint $table) {
            $table->id();
            $table->string('parent_phone', 20)->index();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['parent_phone', 'student_id'], 'uniq_parent_student');
        });
    }
    public function down(): void { Schema::dropIfExists('parent_student'); }
};
