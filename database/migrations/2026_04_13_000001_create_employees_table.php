<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->string('employee_no')->nullable()->unique();
            $table->string('name');
            $table->string('address')->nullable();

            // Assignment
            $table->string('office')->nullable();
            $table->unsignedBigInteger('responsibility_center_id')->nullable();
            $table->foreign('responsibility_center_id')
                ->references('id')->on('responsibility_centers')
                ->nullOnDelete();

            // Classification
            $table->enum('employee_type', ['contract_of_service', 'project_based'])
                ->default('contract_of_service');

            // COS-specific fields (nullable for project-based)
            $table->string('contract_ref_no')->nullable();
            $table->boolean('swa')->default(false);
            $table->string('atm_account_no')->nullable();
            $table->decimal('monthly_compensation', 15, 2)->nullable();
            $table->decimal('deduction_sss', 15, 2)->nullable();
            $table->decimal('deduction_philhealth', 15, 2)->nullable();
            $table->decimal('deduction_hdmf', 15, 2)->nullable();

            // Status
            $table->boolean('is_active')->default(true);

            // Audit
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
        Schema::dropIfExists('employees');
    }
};
