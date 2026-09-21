<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('assistant_chats', function (Blueprint $table) {
            $table->id();
            $table->string('session_key', 64)->index();
            $table->string('user_message', 500);
            $table->string('bot_reply', 2000);
            $table->string('matched_intent', 60)->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('assistant_chats'); }
};
