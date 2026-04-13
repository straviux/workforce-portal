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
        Schema::create('particulars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('responsibility_center_id');
            $table->foreign('responsibility_center_id')->references('id')->on('responsibility_centers')->cascadeOnDelete();
            $table->string('name');
            $table->string('account_code');
            $table->decimal('allotment', 15, 2)->nullable();
            $table->date('date_approved')->nullable();
            $table->date('date_expired')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('particulars');
    }
};
