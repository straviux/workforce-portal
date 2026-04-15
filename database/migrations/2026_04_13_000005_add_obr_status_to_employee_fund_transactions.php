<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_fund_transactions', function (Blueprint $table) {
            $table->string('obr_status')->nullable()->default('No OBR')->after('transaction_status');
        });
    }

    public function down(): void
    {
        Schema::table('employee_fund_transactions', function (Blueprint $table) {
            $table->dropColumn('obr_status');
        });
    }
};
