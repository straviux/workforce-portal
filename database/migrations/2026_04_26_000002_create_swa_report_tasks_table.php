<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swa_report_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('swa_report_id');
            $table->foreign('swa_report_id')->references('id')->on('swa_reports')->cascadeOnDelete();
            $table->unsignedBigInteger('source_task_id')->nullable();
            $table->foreign('source_task_id')->references('id')->on('swa_tasks')->nullOnDelete();
            $table->string('task_name');
            $table->enum('task_type', ['countable', 'check_blank']);
            $table->unsignedTinyInteger('sort_order');
            $table->timestamps();

            $table->unique(['swa_report_id', 'sort_order'], 'swa_report_tasks_report_sort_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swa_report_tasks');
    }
};
