<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_fund_transactions', function (Blueprint $table) {
            $table->string('payee_address')->nullable()->change();
            $table->string('office')->nullable()->change();
            $table->decimal('amount', 15, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_fund_transactions', function (Blueprint $table) {
            $table->string('payee_address')->nullable(false)->change();
            $table->string('office')->nullable(false)->change();
            $table->decimal('amount', 15, 2)->nullable(false)->change();
        });
    }
};
