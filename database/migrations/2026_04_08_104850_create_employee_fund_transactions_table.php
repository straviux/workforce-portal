<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_fund_transactions', function (Blueprint $table) {
            // Primary key & identification
            $table->id();
            $table->string('transaction_id')->unique();
            $table->enum('employee_type', ['contract_of_service', 'project_based']);

            // Payee info
            $table->string('payee_name');
            $table->string('payee_address');
            $table->string('office');
            $table->unsignedBigInteger('responsibility_center');
            $table->foreign('responsibility_center')->references('id')->on('responsibility_centers')->restrictOnDelete();

            // Financial details
            $table->string('account_code')->nullable();
            $table->string('particulars_name')->nullable();
            $table->text('particulars_description')->nullable();  // HTML from Quill
            $table->decimal('amount', 15, 2);
            $table->string('fiscal_year')->nullable();
            $table->string('disbursement_type')->nullable();
            $table->text('explanation')->nullable();

            // OBR / DV references
            $table->string('obr_type')->nullable();
            $table->string('obr_no')->nullable();
            $table->string('dv_no')->nullable();
            $table->date('date_obligated')->nullable();

            // Status & notes
            $table->string('transaction_status')->default('pending'); // pending|approved|active|denied|suspended
            $table->text('remarks')->nullable(); // HTML from Quill

            // File upload token
            $table->string('upload_token')->nullable();
            $table->timestamp('upload_token_expires_at')->nullable();

            // Contract of Service only (nullable for project-based)
            $table->string('employee_id')->nullable();
            $table->string('contract_ref_no')->nullable();
            $table->boolean('swa')->default(false); // Special Work Assignment
            $table->string('atm_account_no')->nullable();
            $table->decimal('monthly_compensation', 15, 2)->nullable();
            $table->decimal('deduction_sss', 15, 2)->nullable();
            $table->decimal('deduction_philhealth', 15, 2)->nullable();
            $table->decimal('deduction_hdmf', 15, 2)->nullable();

            // Audit & soft delete
            $table->unsignedBigInteger('created_by')->nullable();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_fund_transactions');
    }
};
