<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Explicit admin assignments of one Part A office head per office.
     * When a document's office matches an assignment here, that head is
     * used ahead of the office-name match and the per-type default.
     * The head reference nulls on delete so a removed head never blocks
     * the fallback chain; the row itself stays for the admin to fix.
     */
    public function up(): void
    {
        Schema::create('signatory_office_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('office')->unique();
            $table->foreignId('office_head_signatory_id')->nullable()->constrained('signatories')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('signatory_office_mappings');
    }
};
