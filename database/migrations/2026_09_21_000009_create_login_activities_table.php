<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('login_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email', 120)->nullable();
            $table->enum('event', ['login_success','login_failed','logout','password_changed','locked_out']);
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('location', 120)->nullable();
            $table->string('reason', 200)->nullable();
            $table->timestamps();
            $table->index(['event', 'created_at']);
            $table->index('user_id');
        });
    }
    public function down(): void { Schema::dropIfExists('login_activities'); }
};
