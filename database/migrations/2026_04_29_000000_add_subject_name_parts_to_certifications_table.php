<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certifications', function (Blueprint $table) {
            $table->string('subject_firstname')->nullable();
            $table->string('subject_middlename')->nullable();
            $table->string('subject_lastname')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('certifications', function (Blueprint $table) {
            $table->dropColumn([
                'subject_firstname',
                'subject_middlename',
                'subject_lastname',
            ]);
        });
    }
};
