<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per document type holding the centrally configured Part A
     * (office head) signatory preset. Generation paths (SWA, DTR,
     * certification) resolve their snapshot from this table instead of
     * trusting client-supplied signatory fields; fund prints read the same
     * configuration through GET /api/signatory-defaults.
     */
    public function up(): void
    {
        Schema::create('signatory_document_defaults', function (Blueprint $table) {
            $table->id();
            $table->string('document_type')->unique();
            $table->foreignId('office_head_signatory_id')->nullable()->constrained('signatories')->nullOnDelete();
            $table->json('signatory_titles')->nullable();
            $table->boolean('signatory_name_underline')->default(false);
            $table->boolean('signatory_show_designation')->default(true);
            $table->boolean('signatory_show_office')->default(true);
            $table->string('signatory_info_order', 32)->default('designation_first');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatory_document_defaults');
    }
};
