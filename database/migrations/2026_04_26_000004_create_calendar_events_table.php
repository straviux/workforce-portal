<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->id();
            $table->date('event_date')->index();
            $table->string('title');
            $table->enum('event_type', ['legal_holiday', 'local_holiday', 'work_suspension'])->index();
            $table->text('description')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();

            $table->index(['event_date', 'is_active'], 'calendar_events_date_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_events');
    }
};
