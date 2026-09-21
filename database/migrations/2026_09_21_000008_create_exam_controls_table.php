<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('exam_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->string('class', 30)->nullable();
            $table->string('stream', 30)->nullable();
            $table->enum('action', ['opened', 'closed', 'published']);
            $table->text('notes')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_ip', 45)->nullable();
            $table->timestamps();

            $table->index(['exam_id', 'action']);
        });
    }
    public function down(): void { Schema::dropIfExists('exam_controls'); }
};
