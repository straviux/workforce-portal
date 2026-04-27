<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_transaction_employees', function (Blueprint $table) {
            $table->unsignedInteger('lost_hour_minutes')->nullable()->after('deduction_hdmf');
        });
    }

    public function down(): void
    {
        Schema::table('fund_transaction_employees', function (Blueprint $table) {
            $table->dropColumn('lost_hour_minutes');
        });
    }
};
