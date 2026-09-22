<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('canteen_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->default('general'); // snack, drink, meal, stationery
            $table->decimal('price', 10, 2);
            $table->integer('stock')->default(0); // 0 = unlimited
            $table->boolean('active')->default(true);
            $table->boolean('blockable')->default(true); // can parents block this?
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('canteen_items');
    }
};
