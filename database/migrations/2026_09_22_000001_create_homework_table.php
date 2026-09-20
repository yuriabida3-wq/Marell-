<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('homework', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->string('class', 30);
            $table->string('stream', 30)->nullable();
            $table->string('subject', 60);
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('attachment')->nullable();
            $table->date('due_date');
            $table->boolean('published')->default(true);
            $table->timestamps();

            $table->index(['class', 'stream', 'due_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('homework'); }
};
