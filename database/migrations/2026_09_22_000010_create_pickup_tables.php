<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        // Approved people who can pick up a specific student
        Schema::create('approved_pickups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('phone', 20);
            $table->string('relationship', 60); // Mother, Father, Uncle, Driver
            $table->string('id_number', 30)->nullable();
            $table->string('photo')->nullable();
            $table->string('qr_token', 64)->unique();
            $table->boolean('active')->default(true);
            $table->foreignId('created_by_parent')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['student_id', 'active']);
        });

        // Every release event
        Schema::create('pickup_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approved_pickup_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('picker_name', 120);
            $table->string('picker_phone', 20)->nullable();
            $table->string('relationship', 60)->nullable();
            $table->enum('result', ['verified', 'denied', 'manual']);
            $table->text('reason')->nullable();
            $table->foreignId('guard_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('guard_name', 120)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index(['student_id', 'created_at']);
        });

        // Panic alerts
        Schema::create('panic_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_name', 120)->nullable();
            $table->string('location', 120)->default('Main Gate');
            $table->text('note')->nullable();
            $table->string('ip', 45)->nullable();
            $table->unsignedInteger('sms_sent')->default(0);
            $table->unsignedInteger('sms_failed')->default(0);
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // Add pin + role_label already exists. Add pin column for security guards
        Schema::table('users', function (Blueprint $table) {
            $table->string('pin', 80)->nullable()->after('password');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('pin');
        });
        Schema::dropIfExists('panic_alerts');
        Schema::dropIfExists('pickup_logs');
        Schema::dropIfExists('approved_pickups');
    }
};
