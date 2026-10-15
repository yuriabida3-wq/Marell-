<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_callbacks', function (Blueprint $table) {
            $table->id();
            $table->string('checkout_request_id', 80)->nullable()->unique();
            $table->string('mpesa_receipt_number', 50)->nullable()->unique();
            $table->json('payload');
            $table->enum('source', ['secure', 'legacy'])->default('secure');
            $table->string('ip', 45)->nullable();
            $table->timestamp('received_at')->useCurrent();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->nullOnDelete();
            $table->timestamps();

            $table->index('source');
            $table->index('received_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_callbacks');
    }
};
