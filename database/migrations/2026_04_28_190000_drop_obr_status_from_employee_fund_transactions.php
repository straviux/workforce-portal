<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_fund_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('employee_fund_transactions', 'obr_status')) {
                $table->dropColumn('obr_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_fund_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_fund_transactions', 'obr_status')) {
                $table->string('obr_status')->nullable()->default('No OBR')->after('transaction_status');
            }
        });
    }
};
