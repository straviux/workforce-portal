<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swa_reports', function (Blueprint $table) {
            $table->boolean('signatory_name_underline')->default(true)->after('signatory_titles');
        });
    }

    public function down(): void
    {
        Schema::table('swa_reports', function (Blueprint $table) {
            $table->dropColumn('signatory_name_underline');
        });
    }
};