<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Enforce one row per part for Parts B, C and D while keeping Part A
     * multi-row. MySQL has no partial/filtered unique index, so the closest
     * enforceable equivalent is used: a stored generated column that yields
     * the part for B/C/D and NULL otherwise, with a unique index on it.
     * NULLs never conflict in a MySQL unique index, so any number of Part A
     * rows coexist while a second B, C or D row is rejected by the database.
     *
     * Pre-existing duplicates are cleaned up first (lowest id per part is
     * kept, matching the row updateOrCreate used to silently update).
     */
    public function up(): void
    {
        foreach (['B', 'C', 'D'] as $part) {
            $keepId = DB::table('signatories')->where('part', $part)->min('id');

            if ($keepId !== null) {
                DB::table('signatories')
                    ->where('part', $part)
                    ->where('id', '!=', $keepId)
                    ->delete();
            }
        }

        Schema::table('signatories', function (Blueprint $table) {
            $table->string('bcd_part', 1)->nullable()
                ->storedAs("CASE WHEN `part` IN ('B', 'C', 'D') THEN `part` ELSE NULL END");
        });

        Schema::table('signatories', function (Blueprint $table) {
            $table->unique('bcd_part', 'signatories_bcd_part_unique');
        });
    }

    public function down(): void
    {
        Schema::table('signatories', function (Blueprint $table) {
            $table->dropUnique('signatories_bcd_part_unique');
        });

        Schema::table('signatories', function (Blueprint $table) {
            $table->dropColumn('bcd_part');
        });
    }
};
