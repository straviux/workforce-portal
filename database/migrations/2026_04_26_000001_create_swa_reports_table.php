<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swa_reports', function (Blueprint $table) {
            $table->id();
            $table->enum('module_type', ['personal', 'employee']);
            $table->morphs('subject');
            $table->unsignedBigInteger('generated_by')->nullable();
            $table->foreign('generated_by')->references('id')->on('users')->nullOnDelete();
            $table->date('period_start_date');
            $table->date('period_end_date');
            $table->json('work_days');

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->index(['module_type', 'period_start_date', 'period_end_date'], 'swa_reports_module_period_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swa_reports');
    }
};
