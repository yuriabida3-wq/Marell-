<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30);        // e.g. Class 6, Grade 5
            $table->string('stream', 30)->nullable(); // Blue, Green, etc.
            $table->string('level', 30)->nullable(); // Baby, Lower, Upper, JSS
            $table->foreignId('class_teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('capacity')->default(40);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->unique(['name', 'stream']);
        });
    }
    public function down(): void { Schema::dropIfExists('classrooms'); }
};
