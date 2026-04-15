<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_fund_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('employee_record_id')->nullable()->after('employee_type');
            $table->foreign('employee_record_id')->references('id')->on('employees')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_fund_transactions', function (Blueprint $table) {
            $table->dropForeign(['employee_record_id']);
            $table->dropColumn('employee_record_id');
        });
    }
};
