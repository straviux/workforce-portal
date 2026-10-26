<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-document-type signatory defaults are deleted entirely:
     * generation now resolves office head, then main office head, then
     * the first Part A head, with fixed display flags. The table goes
     * away; rolling back recreates it empty (the data is not kept).
     */
    public function up(): void
    {
        Schema::dropIfExists('signatory_document_defaults');
    }

    public function down(): void
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
};
