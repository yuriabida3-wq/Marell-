<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['M-Pesa', 'Bank', 'Cash'])->index();
            $table->string('transaction_code', 50)->nullable()->unique();
            $table->string('checkout_request_id', 80)->nullable()->unique();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->index();
            $table->string('receipt_no', 50)->nullable()->unique();
            $table->unsignedBigInteger('exam_id')->nullable();
            $table->string('term', 20)->nullable();
            $table->string('year', 10)->nullable();
            $table->string('recorded_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
