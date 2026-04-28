<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certifications', function (Blueprint $table) {
            $table->boolean('signatory_show_designation')->default(true)->after('signatory_titles');
            $table->boolean('signatory_show_office')->default(true)->after('signatory_show_designation');
            $table->string('signatory_info_order', 32)->default('designation_first')->after('signatory_show_office');
        });
    }

    public function down(): void
    {
        Schema::table('certifications', function (Blueprint $table) {
            $table->dropColumn([
                'signatory_show_designation',
                'signatory_show_office',
                'signatory_info_order',
            ]);
        });
    }
};