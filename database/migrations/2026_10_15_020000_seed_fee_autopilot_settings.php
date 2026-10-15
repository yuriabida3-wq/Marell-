<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // L6: autopilot must be off until the async refactor is verified.
        // L8: due day moves from hardcoded 10th to a settings value.
        DB::table('settings')->insertOrIgnore([
            [
                'name'       => 'fee_due_day',
                'value'      => '10',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name'       => 'fee_autopilot_enabled',
                'value'      => '0',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->whereIn('name', ['fee_due_day', 'fee_autopilot_enabled'])->delete();
    }
};
