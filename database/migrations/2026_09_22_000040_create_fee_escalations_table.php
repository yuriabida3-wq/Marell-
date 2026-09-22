<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fee_escalations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('stage');
            $table->string('channel', 30);
            $table->string('title', 120);
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'sent', 'failed', 'skipped'])->default('pending');
            $table->text('error')->nullable();
            $table->date('scheduled_for')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'stage']);
            $table->index(['status', 'scheduled_for']);
        });

        Schema::create('fee_reminder_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('stage');
            $table->string('name', 60);
            $table->integer('days_offset');
            $table->string('channel', 30);
            $table->string('template', 500);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        DB::table('fee_reminder_settings')->insert([
            ['stage' => 1, 'name' => '3 days before due',  'days_offset' => -3, 'channel' => 'sms',      'template' => 'Reminder: Fee balance for {student} is KES {balance}. Please pay by {due_date}.', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['stage' => 2, 'name' => 'On due date',        'days_offset' => 0,  'channel' => 'sms',      'template' => 'Fee for {student} is due today. Balance: KES {balance}. Pay via M-Pesa.', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['stage' => 3, 'name' => '3 days late',        'days_offset' => 3,  'channel' => 'whatsapp', 'template' => 'Hi, {student} balance is KES {balance}. Please settle to avoid fine.', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['stage' => 4, 'name' => '7 days late',        'days_offset' => 7,  'channel' => 'voice',    'template' => 'Mzazi wa {student}, salio ni shilingi {balance}. Tafadhali lipa haraka.', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['stage' => 5, 'name' => '10 days late (fine)', 'days_offset' => 10, 'channel' => 'sms',     'template' => 'KES 200 fine applied to {student} due to late payment. Balance now KES {balance}.', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['stage' => 6, 'name' => '14 days late',       'days_offset' => 14, 'channel' => 'sms',      'template' => 'URGENT: {student} balance KES {balance}. Parent meeting required.', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['stage' => 7, 'name' => '21 days late',       'days_offset' => 21, 'channel' => 'sms',      'template' => 'Final notice: {student} balance KES {balance}. Suspension next week if unpaid.', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['stage' => 8, 'name' => '30 days late',       'days_offset' => 30, 'channel' => 'letter',   'template' => 'Formal demand letter for {student}. Balance KES {balance}.', 'active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
    public function down(): void {
        Schema::dropIfExists('fee_reminder_settings');
        Schema::dropIfExists('fee_escalations');
    }
};
