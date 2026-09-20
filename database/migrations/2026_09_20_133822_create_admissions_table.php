<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();
            $table->string('student_name', 120);
            $table->date('dob')->nullable();
            $table->string('class_applying', 30);
            $table->string('parent_name', 120);
            $table->string('parent_phone', 20);
            $table->string('parent_email', 120)->nullable();
            $table->text('message')->nullable();
            $table->boolean('consent')->default(false);
            $table->enum('status', ['new', 'contacted', 'admitted', 'rejected'])->default('new');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('admissions'); }
};
