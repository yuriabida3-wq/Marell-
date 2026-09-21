<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('phone', 20);
            $table->string('id_number', 30)->nullable();
            $table->string('purpose', 200);
            $table->string('host_name', 120)->nullable();      // person they're visiting
            $table->string('host_type', 30)->nullable();       // staff, student, admin
            $table->string('student_adm', 30)->nullable();     // if visiting a student
            $table->string('vehicle_plate', 20)->nullable();
            $table->string('photo')->nullable();
            $table->enum('status', ['expected', 'checked_in', 'checked_out'])->default('expected');
            $table->timestamp('checked_in_at')->nullable();
            $table->timestamp('checked_out_at')->nullable();
            $table->string('badge_no', 20)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'checked_in_at']);
        });
    }
    public function down(): void { Schema::dropIfExists('visitors'); }
};
