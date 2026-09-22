<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {

        // Add wallet columns to students
        Schema::table('students', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->unique()->after('photo');
            $table->decimal('wallet_balance', 10, 2)->default(0)->after('fines_total');
            $table->decimal('auto_reload_threshold', 10, 2)->default(0)->after('wallet_balance');
            $table->decimal('auto_reload_amount', 10, 2)->default(0)->after('auto_reload_threshold');
            $table->string('auto_reload_phone', 20)->nullable()->after('auto_reload_amount');
            $table->boolean('auto_reload_enabled')->default(false)->after('auto_reload_phone');
        });

        // Wallet transactions (loads + spends)
        Schema::create('wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['load', 'spend', 'refund', 'adjustment']);
            $table->decimal('amount', 10, 2);
            $table->decimal('balance_after', 10, 2);
            $table->string('reference', 60)->nullable(); // M-Pesa code, receipt, etc
            $table->string('category', 60)->nullable();  // canteen, sports, uniform, etc
            $table->text('description')->nullable();
            $table->string('recorded_by', 120)->nullable(); // staff name
            $table->foreignId('staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip', 45)->nullable();
            $table->timestamps();
            $table->index(['student_id', 'created_at']);
        });

        // Canteen menu (optional — for quick items)
        Schema::create('canteen_items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80);
            $table->decimal('price', 8, 2);
            $table->string('category', 40)->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        // Insert a few default items
        DB::table('canteen_items')->insert([
            ['name' => 'Chapati',        'price' => 20, 'category' => 'Snacks', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mandazi',        'price' => 15, 'category' => 'Snacks', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Soda 300ml',     'price' => 50, 'category' => 'Drinks', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Juice',          'price' => 40, 'category' => 'Drinks', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Samosa',         'price' => 30, 'category' => 'Snacks', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Lunch Plate',    'price' => 100, 'category' => 'Meals', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    public function down(): void {
        Schema::dropIfExists('canteen_items');
        Schema::dropIfExists('wallet_transactions');
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'wallet_balance', 'auto_reload_threshold', 'auto_reload_amount', 'auto_reload_phone', 'auto_reload_enabled']);
        });
    }
};
