<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dtr_report_daily_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dtr_report_id');
            $table->foreign('dtr_report_id')->references('id')->on('dtr_reports')->cascadeOnDelete();
            $table->date('work_date');

            // Stored as 24-hour "HH:mm" strings — simpler to round-trip with a native
            // <input type="time"> on the frontend than a DB time/datetime cast.
            $table->string('am_arrival', 5)->nullable();
            $table->string('am_departure', 5)->nullable();
            $table->string('pm_arrival', 5)->nullable();
            $table->string('pm_departure', 5)->nullable();

            // Standard CSC Form 48 "Undertime" fields — hours short of the
            // required work time, not a time in/out pair.
            $table->unsignedTinyInteger('undertime_hours')->nullable();
            $table->unsignedTinyInteger('undertime_minutes')->nullable();

            $table->timestamps();

            $table->unique(['dtr_report_id', 'work_date'], 'dtr_daily_values_report_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dtr_report_daily_values');
    }
};
