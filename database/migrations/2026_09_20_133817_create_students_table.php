<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('adm_no', 30)->unique();
            $table->string('name', 120);
            $table->string('class', 30)->index();
            $table->string('stream', 30)->nullable();
            $table->string('parent_name', 120);
            $table->string('parent_phone', 20)->index();
            $table->string('parent_email', 120)->nullable();
            $table->decimal('total_fee', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('photo')->nullable();
            $table->enum('status', ['active', 'suspended', 'graduated'])->default('active');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('students'); }
};
