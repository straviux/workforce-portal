<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('dtr_report_daily_values', function (Blueprint $table) {
            // Official-business/travel note for the day, e.g. "El Nido, Dumaran".
            // When set, this day is excluded from time-in/out and rendered merged
            // with adjacent days that share the same note (see DtrService/DtrWorkspace).
            $table->string('travel_label')->nullable()->after('pm_departure');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dtr_report_daily_values', function (Blueprint $table) {
            $table->dropColumn('travel_label');
        });
    }
};
