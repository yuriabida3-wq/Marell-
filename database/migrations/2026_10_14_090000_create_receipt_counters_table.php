<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_counters', function (Blueprint $table) {
            $table->integer('year')->primary();
            $table->unsignedInteger('last')->default(0);
            $table->timestamps();
        });

        // Backfill from existing REC-YYYY-NNNNN rows (the previous generator format).
        // Demo rows of the form RCP000123 do not match this pattern and are correctly ignored.
        $rows = DB::select(
            "SELECT CAST(SUBSTRING(receipt_no, 5, 4) AS UNSIGNED) AS y, " .
            "       MAX(CAST(SUBSTRING(receipt_no, -5) AS UNSIGNED)) AS m " .
            "FROM payments " .
            "WHERE receipt_no REGEXP '^REC-[0-9]{4}-[0-9]{5}$' " .
            "GROUP BY y"
        );
        foreach ($rows as $r) {
            DB::table('receipt_counters')->insert([
                'year'       => $r->y,
                'last'       => $r->m,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_counters');
    }
};
