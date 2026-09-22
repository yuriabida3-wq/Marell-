<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('staff_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // 'in' or 'out'
            $table->timestamp('scanned_at');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('staff_attendances'); }
};
