<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('confessions', function (Blueprint $table) {
            $table->id();
            $table->string('category', 60);
            $table->text('message');
            $table->string('contact', 120)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->boolean('read')->default(false);
            $table->boolean('flagged')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('confessions'); }
};
