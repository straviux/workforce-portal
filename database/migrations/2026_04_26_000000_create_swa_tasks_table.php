<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('swa_tasks', function (Blueprint $table) {
            $table->id();
            $table->morphs('subject');
            $table->string('task_name');
            $table->enum('task_type', ['countable', 'check_blank']);
            $table->unsignedTinyInteger('sort_order');
            $table->boolean('is_active')->default(true);

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();

            $table->unique(['subject_type', 'subject_id', 'sort_order'], 'swa_tasks_subject_sort_unique');
            $table->index(['subject_type', 'subject_id', 'is_active'], 'swa_tasks_subject_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('swa_tasks');
    }
};
