<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_transaction_employees', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fund_transaction_id');
            $table->foreign('fund_transaction_id')
                ->references('id')->on('employee_fund_transactions')
                ->cascadeOnDelete();

            // Link to employee record (optional)
            $table->unsignedBigInteger('employee_record_id')->nullable();
            $table->foreign('employee_record_id')
                ->references('id')->on('employees')
                ->nullOnDelete();

            // Employee-specific payee info
            $table->string('payee_name');
            $table->string('payee_address')->nullable();
            $table->string('office')->nullable();
            $table->string('employee_id')->nullable();

            // Contract of Service fields
            $table->string('contract_ref_no')->nullable();
            $table->boolean('swa')->default(false);
            $table->string('atm_account_no')->nullable();
            $table->decimal('monthly_compensation', 15, 2)->nullable();
            $table->decimal('deduction_sss', 15, 2)->nullable();
            $table->decimal('deduction_philhealth', 15, 2)->nullable();
            $table->decimal('deduction_hdmf', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_transaction_employees');
    }
};
