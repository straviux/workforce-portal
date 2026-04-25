<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certifications', function (Blueprint $table) {
            $table->id();
            $table->string('certification_type')->index();
            $table->string('subject_name');
            $table->string('designation');
            $table->string('office');
            $table->date('issued_date');

            $table->unsignedBigInteger('office_head_signatory_id')->nullable();
            $table->foreign('office_head_signatory_id')
                ->references('id')
                ->on('signatories')
                ->nullOnDelete();

            $table->string('signatory_name');
            $table->string('signatory_office')->nullable();
            $table->json('signatory_titles')->nullable();

            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certifications');
    }
};
