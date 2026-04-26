<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swa_report_task_daily_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('swa_report_task_id');
            $table->foreign('swa_report_task_id', 'swa_task_daily_values_task_fk')
                ->references('id')
                ->on('swa_report_tasks')
                ->cascadeOnDelete();
            $table->date('work_date');
            $table->decimal('numeric_value', 10, 2)->nullable();
            $table->enum('mark_value', ['check', 'dash'])->nullable();
            $table->timestamps();

            $table->unique(['swa_report_task_id', 'work_date'], 'swa_task_daily_values_task_date_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swa_report_task_daily_values');
    }
};
