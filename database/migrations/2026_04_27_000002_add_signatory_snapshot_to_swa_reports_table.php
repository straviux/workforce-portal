<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swa_reports', function (Blueprint $table) {
            $table->foreignId('office_head_signatory_id')->nullable()->constrained('signatories')->nullOnDelete();
            $table->string('signatory_name')->nullable();
            $table->string('signatory_office')->nullable();
            $table->json('signatory_titles')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('swa_reports', function (Blueprint $table) {
            $table->dropForeign(['office_head_signatory_id']);
            $table->dropColumn([
                'office_head_signatory_id',
                'signatory_name',
                'signatory_office',
                'signatory_titles',
            ]);
        });
    }
};
