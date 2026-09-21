<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // Books (catalog)
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('isbn', 30)->nullable();
            $table->string('title', 200);
            $table->string('author', 120)->nullable();
            $table->string('category', 60)->nullable();
            $table->string('publisher', 120)->nullable();
            $table->year('year')->nullable();
            $table->unsignedInteger('total_copies')->default(1);
            $table->unsignedInteger('available_copies')->default(1);
            $table->string('shelf', 30)->nullable();
            $table->string('cover')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index('title');
            $table->index('category');
        });

        // Loans
        Schema::create('book_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('issued_at');
            $table->date('due_at');
            $table->date('returned_at')->nullable();
            $table->decimal('fine', 10, 2)->default(0);
            $table->boolean('fine_paid')->default(false);
            $table->text('notes')->nullable();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['student_id', 'returned_at']);
            $table->index('due_at');
        });

        // Library settings (fine per day, loan days)
        Schema::create('library_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('loan_days')->default(14);
            $table->decimal('fine_per_day', 10, 2)->default(20);
            $table->unsignedInteger('max_books_per_student')->default(2);
            $table->timestamps();
        });

        // Seed default settings
        DB::table('library_settings')->insert([
            'loan_days' => 14,
            'fine_per_day' => 20,
            'max_books_per_student' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
    public function down(): void {
        Schema::dropIfExists('library_settings');
        Schema::dropIfExists('book_loans');
        Schema::dropIfExists('books');
    }
};
