<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vote_heads', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();
            $table->string('name', 60);
            $table->string('description', 200)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        Schema::create('student_fee_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vote_head_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_due', 12, 2)->default(0);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->timestamps();
            $table->unique(['student_id', 'vote_head_id'], 'uniq_student_vote');
        });
    }
    public function down(): void {
        Schema::dropIfExists('student_fee_votes');
        Schema::dropIfExists('vote_heads');
    }
};
